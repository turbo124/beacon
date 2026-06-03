<?php

namespace Turbo124\Beacon\Tests;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\Test;
use Turbo124\Beacon\ExampleMetric\GenericGauge;
use Turbo124\Beacon\Generator;
use Turbo124\Beacon\Jobs\BatchMetrics;
use Turbo124\Beacon\Jobs\SystemMetric;

class BatchMetricsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'beacon.enabled' => true,
            'beacon.api_key' => 'test-key',
            'beacon.cache_connection' => '',
            'beacon.cache_key' => 'beacon',
            'cache.prefix' => '',
        ]);

        Queue::fake();
    }

    #[Test]
    public function itDeletesOnlyChunksThatWereSentSuccessfully(): void
    {
        $keys = [];
        $values = [];

        for ($i = 1; $i <= 41; $i++) {
            $key = 'beacongauge' . $i;
            $metric = new GenericGauge();
            $metric->name = 'metric.' . $i;

            $keys[] = $key;
            $values[$key] = serialize($metric);
        }

        $redis = new FakeBatchRedisConnection([
            'beacongauge*' => $keys,
        ], $values);

        Redis::shouldReceive('connection')->andReturn($redis);

        $generator = new RecordingBatchGenerator([true, false]);

        (new TestableBatchMetrics($generator))->handle();

        Queue::assertNotPushed(SystemMetric::class);

        $this->assertCount(2, $generator->calls);
        $this->assertCount(40, $generator->calls[0]);
        $this->assertCount(1, $generator->calls[1]);
        $this->assertSame(array_slice($keys, 0, 40), $redis->deleted);
        $this->assertSame([array_slice($keys, 0, 40), array_slice($keys, 40)], $redis->mgetCalls);
    }

    #[Test]
    public function itLeavesKeysInRedisWhenBatchSendFails(): void
    {
        $metric = new GenericGauge();
        $metric->name = 'metric.failed';

        $redis = new FakeBatchRedisConnection([
            'beacongauge*' => ['beacongauge1'],
        ], [
            'beacongauge1' => serialize($metric),
        ]);

        Redis::shouldReceive('connection')->andReturn($redis);

        (new TestableBatchMetrics(new RecordingBatchGenerator([false])))->handle();

        $this->assertSame([], $redis->deleted);
    }
}

class TestableBatchMetrics extends BatchMetrics
{
    public function __construct(private Generator $generator) {}

    protected function makeGenerator(): Generator
    {
        return $this->generator;
    }
}

class RecordingBatchGenerator extends Generator
{
    public array $calls = [];

    public function __construct(private array $results) {}

    public function batchFire($metric_array)
    {
        $this->calls[] = $metric_array;

        return array_shift($this->results) ?? false;
    }
}

class FakeBatchRedisConnection
{
    public array $deleted = [];

    public array $mgetCalls = [];

    public function __construct(private array $keysByPrefix, private array $valuesByKey) {}

    public function keys(string $prefix): array
    {
        return $this->keysByPrefix[$prefix] ?? [];
    }

    public function mget(array $keys): array
    {
        $this->mgetCalls[] = $keys;

        return array_map(fn($key) => $this->valuesByKey[$key] ?? false, $keys);
    }

    public function pipeline(callable $callback): void
    {
        $callback(new FakeBatchRedisPipeline($this));
    }

    public function delete(string $key): void
    {
        $this->deleted[] = $key;
    }
}

class FakeBatchRedisPipeline
{
    public function __construct(private FakeBatchRedisConnection $redis) {}

    public function del(string $key): void
    {
        $this->redis->delete($key);
    }
}

<?php

namespace Turbo124\Beacon\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Turbo124\Beacon\Generator;
use Turbo124\Beacon\ExampleMetric\GenericCounter;
use Turbo124\Beacon\ExampleMetric\GenericGauge;
use Turbo124\Beacon\ExampleMetric\GenericMixedMetric;
use Turbo124\Beacon\ExampleMetric\GenericMultiMetric;
use Turbo124\Beacon\ExampleMetric\GenericStructuredMetric;

/**
 * Guards the Guzzle 7.11/8.0 form_params fix: metric objects are cast to
 * arrays before being sent. This proves the cast keeps the serialized
 * (http_build_query) payload byte-for-byte identical to the previous
 * object-based behaviour, so the collector receives the same wire format.
 */
class GeneratorTest extends TestCase
{
    public static function metricProvider(): array
    {
        $structured = new GenericStructuredMetric();
        $structured->name = 'structured';
        $structured->html = '<p>hi</p>';
        $structured->json = ['a' => 1, 'nested' => ['b' => 2, 'c' => 'three']];

        $mixed = new GenericMixedMetric();
        $mixed->name = 'mixed';
        $mixed->int_metric1 = 5;
        $mixed->string_metric5 = 'value';

        return [
            'gauge'      => [(new GenericGauge())],
            'counter'    => [(new GenericCounter())],
            'multi'      => [(new GenericMultiMetric())],
            'mixed'      => [$mixed],
            'structured' => [$structured],
        ];
    }

    #[Test]
    #[DataProvider('metricProvider')]
    public function castProducesIdenticalWireFormat($metric): void
    {
        $metric->datetime = '2026-06-03 00:00:00';

        $asObject = http_build_query(['metrics' => [$metric]]);
        $asArray  = http_build_query(['metrics' => [(array) $metric]]);

        $this->assertSame($asObject, $asArray);
    }

    #[Test]
    public function castProducesArrayMetrics(): void
    {
        $metric = new GenericGauge();

        $cast = array_map(fn($m) => is_object($m) ? (array) $m : $m, [$metric]);

        $this->assertIsArray($cast[0]);
        $this->assertSame('gauge', $cast[0]['type']);
    }

    #[Test]
    public function batchFireReturnsTrueAndSendsArrayMetrics(): void
    {
        config(['beacon.endpoint' => 'https://collector.test/api']);

        $history = [];
        $mock = new MockHandler([new Response(202)]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        $generator = new Generator(new Client(['handler' => $stack]));
        $metric = new GenericGauge();
        $metric->name = 'system.cpu';

        $this->assertTrue($generator->batchFire([$metric]));
        $this->assertCount(1, $history);
        $this->assertSame('https://collector.test/api/gauge/batch', (string) $history[0]['request']->getUri());
        $this->assertStringContainsString('metrics%5B0%5D%5Btype%5D=gauge', (string) $history[0]['request']->getBody());
    }

    #[Test]
    public function batchFireReturnsFalseWhenTheRequestFails(): void
    {
        config(['beacon.endpoint' => 'https://collector.test/api']);

        $mock = new MockHandler([
            new RequestException('failed', new Request('POST', 'https://collector.test/api/gauge/batch')),
        ]);

        $generator = new Generator(new Client(['handler' => HandlerStack::create($mock)]));

        $this->assertFalse($generator->batchFire([new GenericGauge()]));
    }

    #[Test]
    public function batchFireRoutesMixedTypesToTheirOwnEndpoints(): void
    {
        config(['beacon.endpoint' => 'https://collector.test/api']);

        $history = [];
        $mock = new MockHandler([new Response(202), new Response(202)]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        $generator = new Generator(new Client(['handler' => $stack]));

        $this->assertTrue($generator->batchFire([new GenericGauge(), new GenericCounter()]));
        $this->assertSame('https://collector.test/api/gauge/batch', (string) $history[0]['request']->getUri());
        $this->assertSame('https://collector.test/api/counter/batch', (string) $history[1]['request']->getUri());
    }
}

<?php

namespace Turbo124\Beacon\Jobs;

use Turbo124\Beacon\Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades;

class BatchMetrics implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct() {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $summary = $this->emptySummary();

        if (!config('beacon.enabled') || empty(config('beacon.api_key'))) {
            return $summary;
        }

        foreach (array_keys($summary) as $type) {

            $redis = Facades\Redis::connection(config('beacon.cache_connection', ''));

            $prefix = config('cache.prefix') . config('beacon.cache_key') . $type . '*';

            $keys = $redis->keys($prefix);

            if (!is_array($keys)) {
                $summary[$type]['errors'][] = 'Redis keys() did not return an array.';
                continue;
            }

            $summary[$type]['pending'] = count($keys);

            if (count($keys) === 0) {
                continue;
            }

            $generator = $this->makeGenerator();

            foreach (array_chunk($keys, 40) as $keyChunk) {
                $metrics = $redis->mget($keyChunk);

                if (!is_array($metrics)) {
                    $summary[$type]['retained'] += count($keyChunk);
                    $summary[$type]['errors'][] = 'Redis mget() did not return an array.';
                    continue;
                }

                $metrics = $this->unserializeMetrics($metrics);

                if (count($metrics) === 0) {
                    $summary[$type]['retained'] += count($keyChunk);
                    $summary[$type]['errors'][] = 'No valid cached metrics could be unserialized.';
                    continue;
                }

                $summary[$type]['attempted'] += count($metrics);

                if ($generator->batchFire($metrics) === true) {
                    $this->deleteKeys($redis, $keyChunk);
                    $summary[$type]['deleted'] += count($keyChunk);
                    continue;
                }

                $summary[$type]['retained'] += count($keyChunk);
                $summary[$type]['errors'][] = $generator->lastErrorMessage() ?? 'Batch send was not acknowledged.';
            }
        }

        return $summary;
    }

    protected function makeGenerator(): Generator
    {
        return new Generator();
    }

    private function emptySummary(): array
    {
        return array_fill_keys(
            ['counter', 'gauge', 'multi_metric', 'mixed_metric', 'structured_metric'],
            [
                'pending' => 0,
                'attempted' => 0,
                'deleted' => 0,
                'retained' => 0,
                'errors' => [],
            ]
        );
    }

    private function unserializeMetrics(array $values): array
    {
        $metrics = [];

        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $metric = @unserialize($value);

            if (is_object($metric) || is_array($metric)) {
                $metrics[] = $metric;
            }
        }

        return $metrics;
    }

    private function deleteKeys($redis, array $keys): void
    {
        $redis->pipeline(function ($pipe) use ($keys) {
            foreach ($keys as $key) {
                $pipe->del($key);
            }
        });
    }
}

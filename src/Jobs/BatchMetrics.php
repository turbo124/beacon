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
        if (!config('beacon.enabled') || empty(config('beacon.api_key'))) {
            return;
        }

        $metric_types = ['counter', 'gauge', 'multi_metric', 'mixed_metric', 'structured_metric'];

        foreach ($metric_types as $type) {

            $redis = Facades\Redis::connection(config('beacon.cache_connection', ''));

            $prefix = config('cache.prefix') . config('beacon.cache_key') . $type . '*';

            $keys = $redis->keys($prefix);

            if (!is_array($keys) || count($keys) === 0) {
                continue;
            }

            $generator = $this->makeGenerator();

            foreach (array_chunk($keys, 40) as $keyChunk) {
                $metrics = $redis->mget($keyChunk);

                if (!is_array($metrics)) {
                    continue;
                }

                $metrics = $this->unserializeMetrics($metrics);

                if (count($metrics) === 0) {
                    continue;
                }

                if ($generator->batchFire($metrics) === true) {
                    $this->deleteKeys($redis, $keyChunk);
                }
            }
        }
    }

    protected function makeGenerator(): Generator
    {
        return new Generator();
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

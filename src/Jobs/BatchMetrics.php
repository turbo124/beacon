<?php

namespace Turbo124\Beacon\Jobs;

use Turbo124\Beacon\Generator;
use Turbo124\Beacon\Jobs\SystemMetric;
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

    public function __construct()
    {
    }

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

        SystemMetric::dispatch();

        $metric_types = ['counter', 'gauge', 'multi_metric', 'mixed_metric', 'structured_metric'];

        foreach ($metric_types as $type) {

            $redis = Facades\Redis::connection(config('beacon.cache_connection',''));

            $prefix = config('cache.prefix').config('beacon.cache_key').$type.'*';

            $keys = $redis->keys($prefix);

            $metrics = false;

            if (count($keys) > 0) {
                $metrics = $redis->mget($keys);

                $redis->pipeline(function ($pipe) use ($keys) {
                    foreach ($keys as $key) {
                        $pipe->del($key);
                    }
                });
            }

            if (!is_array($metrics)) {
                continue;
            }

            foreach($metrics as $key => $value)
                $metrics[$key] = unserialize($value);

            $generator = new Generator();

            $generator->batchFire($metrics); 
        }
    }
}

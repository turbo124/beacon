<?php

namespace Turbo124\Collector\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Turbo124\Collector\Collector\Generator;

class BatchMetrics
{
    use Dispatchable;

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
        $metric_types = ['counter', 'gauge', 'multi_metric', 'mixed_metric'];

        foreach($metric_types as $type)
        {
            $metrics = Cache::get(config('collector.cache_key') . '_' . $type);
      
            if(!is_array($metrics))
                return;
            
            $generator = new Generator();
            $generator->batchFire($metrics);

            Cache::put(config('collector.cache_key') . '_' . $type, []);
        }
    }
}

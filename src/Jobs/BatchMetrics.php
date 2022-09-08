<?php

namespace Turbo124\Beacon\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Turbo124\Beacon\Generator;
use Turbo124\Beacon\Jobs\SystemMetric;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class BatchMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

        if(!config('beacon.enabled') || empty(config('beacon.api_key')))
            return;
        
        SystemMetric::dispatch();
        
        $metric_types = ['counter', 'gauge', 'multi_metric', 'mixed_metric'];

        foreach($metric_types as $type)
        {
            $metrics = Cache::get(config('beacon.cache_key') . '_' . $type);
      
            if(!is_array($metrics))
                continue;
            
            $generator = new Generator();

            $batch_of = 40;
            $batch = array_chunk($metrics, $batch_of);

            foreach($batch as $b) {

                $generator->batchFire($b);

            }

            Cache::put(config('beacon.cache_key') . '_' . $type, []);
        }

    }
}

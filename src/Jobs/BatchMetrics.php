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

        $metrics = Cache::get('collector');

        $generator = new Generator();
        $generator->batchFire($metrics);

        Cache::forget('collector');
        
    }
}

<?php

namespace Turbo124\Collector\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Turbo124\Collector\Collector\Generator;

class SystemMetric
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

        foreach(config('collector.system_logging') as $sys_log)
            $sys_log::dispatchNow();

    }
}

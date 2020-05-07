<?php

namespace Turbo124\Beacon\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Turbo124\Beacon\Generator;

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

        foreach(config('beacon.system_logging') as $sys_log)
            $sys_log::dispatchNow();

    }
}

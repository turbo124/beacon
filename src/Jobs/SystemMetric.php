<?php

namespace Turbo124\Beacon\Jobs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Turbo124\Beacon\Generator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;


class SystemMetric implements ShouldQueue
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

        foreach(config('beacon.system_logging') as $sys_log)
            $sys_log::dispatch();

    }
}

<?php

namespace Turbo124\Beacon\Jobs\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Turbo124\Beacon\Collector;
use Turbo124\Beacon\Generator;
use Turbo124\Beacon\ExampleMetric\GenericGauge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class CpuMetric implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $tries = 1;

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
        $load = sys_getloadavg();

        if(is_array($load))
            $cpuload = $load[1]; //5 minute average CPU load represented as a decimal between 0 and 1
        else
            $cpuload = 0;

        $metric = new GenericGauge();
        $metric->name = 'system.cpu';
        $metric->metric = $cpuload; 

        $collector = new Collector();
        $collector->create($metric)
        ->batch();
    }
}

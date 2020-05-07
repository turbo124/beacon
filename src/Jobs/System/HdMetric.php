<?php

namespace Turbo124\Beacon\Jobs\System;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Turbo124\Beacon\Collector;
use Turbo124\Beacon\Generator;
use Turbo124\Beacon\ExampleMetric\GenericGauge;
use Turbo124\Beacon\ExampleMetric\GenericMultiMetric;

class HdMetric
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
        
        $hdd_free = round(disk_free_space("/"), 2);
        $hdd_total = round(disk_total_space("/"), 2);

        $hdd_used = $hdd_total - $hdd_free;
        $hdd_percent = round(sprintf('%.2f',($hdd_used / $hdd_total) * 100), 2);

        $metric = new GenericMultiMetric();
        $metric->name = 'system.hd';
        $metric->metric1 = $hdd_total; 
        $metric->metric2 = $hdd_free; 
        $metric->metric3 = $hdd_used; 
        $metric->metric4 = $hdd_percent; 

        $collector = new Collector();
        $collector->create($metric)
        ->batch();
    }
}


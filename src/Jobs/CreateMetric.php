<?php

namespace Turbo124\Beacon\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Turbo124\Beacon\Generator;

class CreateMetric
{
    use Dispatchable;

    protected $metric;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($metric)
    {
        $this->metric = $metric;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $generator = new Generator();
        $generator->fire($this->metric);
    }
}

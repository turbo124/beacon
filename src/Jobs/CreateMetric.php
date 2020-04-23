<?php

namespace Turbo124\Collector\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;

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

    }
}

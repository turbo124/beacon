<?php

namespace Turbo124\Beacon;

use Illuminate\Support\Facades\Cache;
use Turbo124\Beacon\Generator;
use Turbo124\Beacon\Jobs\CreateMetric;

class Collector
{

    public $metric;

    public function __construct()
    {
    }

    public function create($metric)
    {
        date_default_timezone_set('UTC');

        $this->metric = $metric;
        $this->metric->datetime = date("Y-m-d H:i:s");

        return $this;
    }

    public function increment()
    {
        $this->metric->metric++;

        return $this;
    }

    public function decrement()
    {
        $this->metric->metric--;

        return $this;
    }

    public function setCount($count)
    {
        $this->metric->metric = $count;

        return $this;
    }

    public function send()
    {
        
        if(!config('beacon.enabled') || empty(config('beacon.api_key')))
            return;

        $generator = (new Generator())->fire($this->metric);

    }

    public function queue()
    {

        if(!config('beacon.enabled') || empty(config('beacon.api_key')))
            return;
        
        CreateMetric::dispatch($this->metric);

    }

    public function probe($request, string $ip, bool $ban = false): self
    {
        // if($request->method() != 'GET')
        //     return $this;

        // if($ban && \Turbo124\Beacon\Jobs\Probe::find(urldecode($request->path()))){
        //     //write to denylist, and queue a reload
        // }

        return $this;
    }

    public function batch()
    {

        if(!config('beacon.enabled') || empty(config('beacon.api_key')))
            return;

        Cache::put(config('beacon.cache_key') . $this->metric->type.microtime(true), $this->metric, 1800);

    }
}

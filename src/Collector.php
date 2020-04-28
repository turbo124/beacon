<?php

namespace Turbo124\Collector;

use Illuminate\Support\Facades\Cache;
use Turbo124\Collector\Collector\Generator;
use Turbo124\Collector\Jobs\CreateMetric;

class Collector
{

    public $metric;

    public function __construct()
    {
    }

    public function create($metric)
    {
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
        if(!config('collector.enabled'))
            return;

        $generator = new Generator();
        $generator->fire($this->metric);
    }

    public function queue()
    {
        if(!config('collector.enabled'))
            return;
        
        CreateMetric::dispatch($this->metric);
    }

    public function batch()
    {

        if(!config('collector.enabled'))
            return;

        $data = Cache::get(config('collector.cache_key') . '_' . $this->metric->type);

        if(is_array($data)){
            $data[] = $this->metric;
        }
        else {
            $data = [];
            $data[] = $this->metric;
        }

        Cache::put(config('collector.cache_key') . '_' . $this->metric->type, $data);
    }
}

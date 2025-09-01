<?php

namespace Turbo124\Beacon\Commands;

use App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades;

class CountMetrics extends Command
{
    /**
     * @var string
     */
    protected $name = 'beacon:count';

    /**
     * @var string
     */
    protected $description = 'Counting any analytics in the cache';

    protected $log = '';

    public function handle()
    {
        $this->logMessage('Counting metrics');
            
        $metric_types = ['counter', 'gauge', 'multi_metric', 'mixed_metric', 'structured_metric'];

        foreach ($metric_types as $type) {

            
            $redis = Facades\Redis::connection(config('beacon.cache_connection',''));

            $prefix = config('cache.prefix').config('beacon.cache_key').$type.'*';

            $keys = $redis->keys($prefix);

            $this->logMessage("{$type} - ".count($keys)." keys");

        }

        $this->logMessage('Finished Counting metrics');

    }

    private function logMessage($str)
    {
        $str = date('Y-m-d h:i:s') . ' ' . $str;
        $this->info($str);
        $this->log .= $str . " \n";
    }
}

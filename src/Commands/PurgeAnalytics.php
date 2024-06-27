<?php

namespace Turbo124\Beacon\Commands;

use App;
use Illuminate\Console\Command;
use Illuminate\Support\Facades;

class PurgeAnalytics extends Command
{
    /**
     * @var string
     */
    protected $name = 'beacon:purge';

    /**
     * @var string
     */
    protected $description = 'Purging any analytics in the cache';

    protected $log = '';

    public function handle()
    {
        $this->logMessage('Purging Data');
            
        $metric_types = ['counter', 'gauge', 'multi_metric', 'mixed_metric'];

        foreach ($metric_types as $type) {

            $this->logMessage("purging {$type}");
            
            $redis = Facades\Redis::connection(config('beacon.cache_connection',''));

            $prefix = config('cache.prefix').config('beacon.cache_key').$type.'*';

            $keys = $redis->keys($prefix);

            if (count($keys) > 0) {
                $redis->pipeline(function ($pipe) use ($keys) {
                    foreach ($keys as $key) {
                        $pipe->del($key);
                    }
                });
            }

        }

        $this->logMessage('Finished Purging Data');

    }

    private function logMessage($str)
    {
        $str = date('Y-m-d h:i:s') . ' ' . $str;
        $this->info($str);
        $this->log .= $str . " \n";
    }
}

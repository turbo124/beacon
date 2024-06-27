<?php

namespace Turbo124\Beacon\Commands;

use Illuminate\Console\Command;
use Turbo124\Beacon\Jobs\BatchMetrics;
use Illuminate\Support\Facades;

class ForceSend extends Command
{
    /**
     * @var string
     */
    protected $name = 'beacon:force-send';

    /**
     * @var string
     */
    protected $description = 'Forces the beacon queue to send data to the endpoint.';

    protected $log = '';

    public function handle()
    {
        $this->logMessage('Sending Data');

        $metric_types = ['counter', 'gauge', 'multi_metric', 'mixed_metric'];

        foreach($metric_types as $type)
        {
            $redis = Facades\Redis::connection(config('beacon.cache_connection',''));

            $prefix = config('cache.prefix').config('beacon.cache_key').$type.'*';

            $metrics = $redis->keys($prefix);

            if(is_array($metrics))
                $this->logMessage("I have " . count($metrics) . "pending to be sent");

        }

         (new BatchMetrics())->handle();

        $this->logMessage(date('Y-m-d h:i:s') . ' Sent Data!!');

        foreach($metric_types as $type)
        {
            $redis = Facades\Redis::connection(config('beacon.cache_connection',''));

            $prefix = config('cache.prefix').config('beacon.cache_key').$type.'*';

            $metrics = $redis->keys($prefix);

            if(is_array($metrics))
                $this->logMessage("I have " . count($metrics) . "pending to be sent");
        }


        
    }

    private function logMessage($str)
    {
        $str = date('Y-m-d h:i:s') . ' ' . $str;
        $this->info($str);
        $this->log .= $str . "\n";
    }
}

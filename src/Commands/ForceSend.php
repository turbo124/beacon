<?php

namespace Turbo124\Beacon\Commands;

use App;
use Illuminate\Console\Command;
use Turbo124\Beacon\Jobs\BatchMetrics;


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



    public function handle()
    {
        $this->logMessage(date('Y-m-d h:i:s') . ' Sending Data');

        $metric_types = ['counter', 'gauge', 'multi_metric', 'mixed_metric'];

        foreach($metric_types as $type)
        {
            $metrics = Cache::get(config('beacon.cache_key') . '_' . $type);
        }

        $this->logMessage("I have " . count($metrics) . "pending to be sent");

        BatchMetrics::dispatchNow();

        $this->logMessage(date('Y-m-d h:i:s') . ' Sent Data!!');

        foreach($metric_types as $type)
        {
            $metrics = Cache::get(config('beacon.cache_key') . '_' . $type);
        }

        $this->logMessage("I have " . count($metrics) . "pending to be sent");
    }
}

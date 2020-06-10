<?php

namespace Turbo124\Beacon\Jobs\Database\MySQL;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Turbo124\Beacon\Collector;
use Turbo124\Beacon\ExampleMetric\GenericGauge;
use Turbo124\Beacon\ExampleMetric\GenericMixedMetric;
use Turbo124\Beacon\Generator;
use Turbo124\Beacon\Jobs\Database\Traits\StatusVariables;

class DbStatus
{
    use Dispatchable, StatusVariables;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $connection;

    private $name;
    
    private $force_send;

    public function __construct(string $connection, string $name, bool $force_send = false)
    {
        $this->connection = $connection;

        $this->name = $name;

        $this->force_send = $force_send;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        config(['database.default' => $this->connection]);

        $db_status = $this->checkDbConnection();

        $metric = new GenericGauge();
        $metric->name = $this->name;
        $metric->metric = (int)$db_status;

        $collector = (new Collector());

        if($this->force_send || !$db_status){ //if there is no DB connection, then we MUST fire immediately!!
            (new Collector())->create($metric)->send();
        }
        else{
            (new Collector())->create($metric)->batch();
        }


    }


}
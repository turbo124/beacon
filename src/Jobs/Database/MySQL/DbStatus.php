<?php

namespace Turbo124\Beacon\Jobs\Database\MySQL;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Turbo124\Beacon\Collector;
use Turbo124\Beacon\ExampleMetric\GenericGauge;
use Turbo124\Beacon\ExampleMetric\GenericMixedMetric;
use Turbo124\Beacon\ExampleMetric\GenericMultiMetric;
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

        $variables = $this->getSlaveVariables();

        if($variables)
        {

            $metric = new GenericMixedMetric();
            $metric->name = 'database.slave_status.'.$this->connection;
            $metric->string_metric5 = $variables->Master_Host; 
            $metric->string_metric6 = $variables->Slave_IO_Running; 
            $metric->string_metric7 = $variables->Slave_SQL_Running; 
            $metric->string_metric8 = substr($variables->Last_Error, 0, 150); 

            $collector = new Collector();
            $collector->create($metric)
            ->batch();

        }

        $status_variables = $this->getVariables();

        if($status_variables)
        {
            $metric = new GenericMultiMetric();
            $metric->name = 'database.performance.'.$this->connection;
            $metric->metric1 = $status_variables->Innodb_data_read;
            $metric->metric2 = $status_variables->Innodb_data_writes;
            $metric->metric3 = $status_variables->Max_used_connections;
            $metric->metric4 = $status_variables->Table_locks_immediate;
            $metric->metric5 = $status_variables->Table_locks_waited;

            
            $collector = new Collector();
            $collector->create($metric)
            ->batch();
        }

    }


}
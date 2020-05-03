<?php

namespace Turbo124\Collections\System;

use Turbo124\Collector\Collector;
use Turbo124\Collector\ExampleMetric\GenericGauge;

class Cpuload
{

	public function run()
	{
		$load = sys_getloadavg();

		if(is_array($load))
			$cpuload = $load[0]; //1 minute average CPU load represented as a decimal between 0 and 1
		else
			$cpuload = 0;

		$metric = new GenericGauge();
		$metric->name = 'system.cpu';
		$metric->metric = $cpuload; 

 		$collector = new Collector();
 		$collector->create($metric)
 		->batch();
	}

}
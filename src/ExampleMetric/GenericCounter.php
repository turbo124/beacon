<?php

namespace Turbo124\Collector\ExampleMetric;

class GenericCounter 
{

	/**
	 * The name of the counter
	 * @var string
	 */
	public $name = '';

	/**
	 * The description of what this counter means
	 * 
	 * @var string
	 */
	public $description = '';

	/**
	 * The X-Axis label
	 * 
	 * @var string
	 */
	public $x_axis = '';

	/**
	 * The Y-Axis label
	 * 
	 * @var string
	 */
	public $y_axis = '';

	/**
	 * The type of Sample
	 * 	- counter
	 * 	- gauge
	 * 	- histogram
	 * 	
	 * @var string
	 */
	public $type = '';

	/**
	 * The api_key for authorization
	 * 
	 * @var string
	 */
	public $api_key = '';

	/**
	 * The datetime of the counter measurement
	 *
	 * date("Y-m-d H:i:s")
	 * 
	 * @var DateTime 
	 */
	public $datetime;

	/**
	 * The increment amount... should always be 
	 * set to 0
	 * 
	 * @var integer
	 */
	public $counter = 0;

}
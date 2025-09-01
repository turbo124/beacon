<?php

namespace Turbo124\Beacon\ExampleMetric;

class GenericStructuredMetric
{
    /**
     * The type of Sample
     *
     * Structured metric allows for a more complex metric to be sent either JSON / HTML
     *
     * 	- counter
     *
     * @var string
     */
    public $type = 'structured_metric';

    /**
     * The name of the structured_metric
     * @var string
     */
    public $name = '';

    /**
     * The datetime of the structured_metric measurement
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
     * @var string
     */
    public $html = '';

        
    /**
     * json
     *
     * @var array
     */
    public $json = [];

}

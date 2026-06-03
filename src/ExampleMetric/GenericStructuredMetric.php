<?php

namespace Turbo124\Beacon\ExampleMetric;

class GenericStructuredMetric
{
    /**
     * The type of Sample
     *
     * Structured metric allows for a more complex metric to be sent either JSON / HTML
     *
     * 	- structured_metric
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
     * @var string|null
     */
    public $datetime;

    /**
     * The HTML content for this metric
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

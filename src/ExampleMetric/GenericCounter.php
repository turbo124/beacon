<?php

namespace Turbo124\Beacon\ExampleMetric;

class GenericCounter
{
    /**
     * The type of Sample
     *
     * Monotonically incrementing counter
     *
     * 	- counter
     *
     * @var string
     */
    public $type = 'counter';

    /**
     * The name of the counter
     * @var string
     */
    public $name = '';

    /**
     * The datetime of the counter measurement
     *
     * date("Y-m-d H:i:s")
     *
     * @var string|null
     */
    public $datetime;

    /**
     * The increment amount... should always be
     * set to 0
     *
     * @var integer
     */
    public $metric = 0;

}

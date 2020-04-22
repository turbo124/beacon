<?php

namespace Turbo124\Collector;

class Collector
{
    private $config;

    /**
     * The Collector type
     * - counter
     * - histogram
     * - gauge
     * 
     * @var string
     * 
     */
    private $type;

    /**
     * The Collector name
     * @var string
     * 
     */
    private $name;

    private $generator;

    public function __construct()
    {
    }

    /**
     * Create a collector message
     * 
     * parameters
     * 	
     * @param  [type] $config [description]
     * @return [type]         [description]
     */
    public function create()
    {
    	$this->generator = new Generator();

    	return $this;
    }

    public function setType(string $collector_type)
    {
    	$this->type = $collector_type;
    
    	return $this;
    }

    public function setName(string $collector_name)
    {
    	$this->name = $collector_name;
    
    	return $this;
    }

    public function getType()
    {
    	return $this->type;
    }

    public function getName()
    {
    	return $this->name;
    }

    public function inc()
    {
    	$this->generator->increment($this);
    }

    public function dec()
    {
    	$this->generator->decrement($this);
    }

}

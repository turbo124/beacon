<?php

return [

    /**
     * Enable or disable the collector
     */
    'enabled'   =>  true,

	/**
	 * The API endpoint for logs
	 */
    'endpoint'  => 'http://collector.test:8000',

    /**
     * Your API key
     */
    'api_key'   => '123456',

    /**
     * Should batch requests
     */
    'batch'     => true,

    /**
     * The default key used to store
     * metrics for batching
     */
    'cache_key' => 'collector',

];
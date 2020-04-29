<?php

return [

    /**
     * Enable or disable the collector
     */
    'enabled'   =>   true,

    /**
     * The API endpoint for logs
     */
    'endpoint'  => 'https://app.lightlogs.com/api',

    /**
     * Your API key
     */
    'api_key'   => '',

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
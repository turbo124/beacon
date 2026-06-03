<?php

return [

    /**
     * Enable or disable the beacon
     */
    'enabled'   => env('BEACON_ENABLED', false),

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
    'cache_key' => 'beacon',

    /**
     * Configure you cache connection here
     */
    'cache_connection' => '',

    /**
     * Synthetic host system metrics are disabled by default.
     */
    'system_logging' => [],

    'database' => [
        'mysql' => [
            'master' => 'master_connection',
            'slave' => 'slave_connection',
        ],
    ],

];
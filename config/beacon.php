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
     * Determines whether to log the 
     * host system variables using
     * the built in metrics.
     */
    'system_logging' => [
        'Turbo124\Beacon\Jobs\System\CpuMetric',
        'Turbo124\Beacon\Jobs\System\HdMetric',
        'Turbo124\Beacon\Jobs\System\MemMetric',
    ],

    'database' => [
        'mysql' => [
            'master' => 'master_connection',
            'slave' => 'slave_connection',
        ],
    ],

];
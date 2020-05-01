<?php

namespace Illuminate\Support\Facades;

class LightLogs extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'collector';
    }
}

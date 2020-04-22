<?php

namespace Turbo124\Collector;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Turbo124\Collector\Skeleton\SkeletonClass
 */
class CollectorFacade extends Facade
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

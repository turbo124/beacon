<?php

namespace Turbo124\Beacon\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Turbo124\Beacon\CollectorServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            CollectorServiceProvider::class,
        ];
    }
}

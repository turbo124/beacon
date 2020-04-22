<?php

namespace Turbo124\Collector\Tests;

use Orchestra\Testbench\TestCase;
use Turbo124\Collector\CollectorServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [CollectorServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}

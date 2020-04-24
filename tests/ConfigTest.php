<?php

namespace Turbo124\Collector\Tests;

use Orchestra\Testbench\TestCase;
use Turbo124\Collector\Collector;
use Turbo124\Collector\CollectorServiceProvider;
use Turbo124\Collector\Collector\Generator;

class ConfigTest extends TestCase
{
    /** @test */
	public function testValidInstanceType()
	{
		$collector = new Collector;
		$this->assertTrue($collector instanceof Collector);
	}
}

<?php

namespace Turbo124\Beacon\Tests;

use PHPUnit\Framework\TestCase;
use Turbo124\Beacon\Collector;
use Turbo124\Beacon\CollectorServiceProvider;
use Turbo124\Beacon\Beacon\Generator;

class ConfigTest extends TestCase
{
    /** @test */
	public function testValidInstanceType()
	{
		$collector = new Collector;
		$this->assertTrue($collector instanceof Collector);
	}
}

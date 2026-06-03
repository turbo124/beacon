<?php

namespace Turbo124\Beacon\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Turbo124\Beacon\Collector;

class ConfigTest extends TestCase
{
    #[Test]
	public function testValidInstanceType()
	{
		$collector = new Collector;
		$this->assertTrue($collector instanceof Collector);
	}
}

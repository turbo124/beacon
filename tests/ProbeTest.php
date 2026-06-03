<?php

namespace Turbo124\Beacon\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Turbo124\Beacon\Jobs\Probe;

class ProbeTest extends TestCase
{
    #[Test]
    public function testArrayFlipperTrue()
    {
        $needle = '/~use.php';

        $this->assertTrue(Probe::find($needle));
    }

    #[Test]
    public function testArrayFlipperFalse()
    {
        $needle = 'merpyderpy.kdk';

        $this->assertFalse(Probe::find($needle));
    }
}

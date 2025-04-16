<?php

namespace Turbo124\Beacon\Tests;

use PHPUnit\Framework\TestCase;
use Turbo124\Beacon\Jobs\Probe;

class ProbeTest extends TestCase
{
    public function testArrayFlipperTrue()
    {
        $t = microtime(true);
        
        $needle = '/~use.php';

        $this->assertTrue(Probe::find($needle));

        echo microtime(true) - $t.PHP_EOL;
    }

    public function testArrayFlipperFalse()
    {
        $t = microtime(true);

        $needle = 'merpyderpy.kdk';

        $this->assertFalse(Probe::find($needle));

        echo microtime(true) - $t.PHP_EOL;

    }
}

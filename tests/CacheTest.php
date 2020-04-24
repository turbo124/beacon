<?php

namespace Turbo124\Collector\Tests;

use Illuminate\Support\Facades\Cache;
use Orchestra\Testbench\TestCase;
use Turbo124\Collector\Collector;
use Turbo124\Collector\CollectorServiceProvider;
use Turbo124\Collector\Collector\Generator;

class CacheTest extends TestCase
{
    /** @test */
	public function testCacheGetAndPut()
	{
		$test_array = ['a' => 'b', 'c'=>'d'];

        config(['cache.default' => 'array']);

        $data = Cache::tags(['collector'])->get('collector');

        if(is_array($data)){
            $data[] = $test_array;
        }
        else {
            $data = [];
            $data[] = $test_array;
        }

        Cache::tags(['collector'])->put('collector', $data);

        $test_data = Cache::tags(['collector'])->get('collector');

        $this->assertTrue(is_array($test_data));

        $this->assertEquals($test_data[0]['a'], 'b');
	}
}

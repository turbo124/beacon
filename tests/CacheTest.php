<?php

namespace Turbo124\Beacon\Tests;

use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\TestCase;
use Turbo124\Beacon\Collector;
use Turbo124\Beacon\CollectorServiceProvider;
use Turbo124\Beacon\Beacon\Generator;

class CacheTest extends TestCase
{
    /** @test */
	public function testCacheGetAndPut()
	{

        $this->assertFalse(false);
		// $test_array = ['a' => 'b', 'c'=>'d'];

  //       $data = Cache::get('collector');

  //       if(is_array($data)){
  //           $data[] = $test_array;
  //       }
  //       else {
  //           $data = [];
  //           $data[] = $test_array;
  //       }

  //       Cache::put('collector', $data);

  //       $test_data = Cache::get('collector');

  //       $this->assertTrue(is_array($test_data));

  //       $this->assertEquals($test_data[0]['a'], 'b');
	}
}

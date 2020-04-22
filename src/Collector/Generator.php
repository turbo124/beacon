<?php

namespace Turbo124\Collector\Collector;

use Turbo124\Collector\Collector;

class Generator
{

	public function increment(Collector $collector)
	{

	}

	public function decrement(Collector $collector)
	{

	}

	private function endPoint()
	{
		return config('collector.endpoint');
	}

	private function apiKey()
	{
		return config('collect.api_key');
	}
}

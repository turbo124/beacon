<?php

namespace Turbo124\Collector\Collector;

use Turbo124\Collector\Collector;

class Generator
{

	public function increment(Collector $collector)
	{
		date_default_timezone_set('UTC'); 

		$data = array_merge($this->buildRequest($collector), ['action' => 'increment', 'timestamp'=> date("Y-m-d H:i:s")]);

		return $this->fire($data);
	}

	public function decrement(Collector $collector)
	{
		$data = array_merge($this->buildRequest($collector), ['action' => 'decrement', 'timestamp'=> date("Y-m-d H:i:s")]);
	}

	private function endPoint()
	{
		return config('collector.endpoint') . '/collect';
	}

	private function apiKey()
	{
		return config('collect.api_key');
	}

	private function httpClient()
	{
		return new \GuzzleHttp\Client(['headers' => ['X-API-KEY' => $this->apiKey()]]);
	}

	private function buildRequest(Collector $collector)
	{
		return [
			'name' => $collector->getName(),
			'type' => $collector->getType(),
		];
	}

	private function fire(array $data)
	{
		$client = $this->httpClient();	
		$response = $client->request('POST',$this->endPoint(), ['form_params' => $data]);
		return $this->handleResponse($response);
	}

	private function handleResponse($response)
	{
		switch ($response->getStatusCode()) {
			case 200:
				# code...
				break;
			
			default:
				//we may need to cache the action and retry until we get a 200
				break;
		}
	}

}

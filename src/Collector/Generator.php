<?php

namespace Turbo124\Collector\Collector;

use Turbo124\Collector\Collector;

class Generator
{
	private function endPoint()
	{
		return config('collector.endpoint') . '/collect';
	}

	private function batchEndPoint()
	{
		return config('collector.endpoint') . '/collect/batch';
	}

	private function apiKey()
	{
		return config('collector.api_key');
	}

	private function httpClient()
	{
		return new \GuzzleHttp\Client(['headers' => ['X-API-KEY' => $this->apiKey()]]);
	}

	public function fire($metric)
	{
		$client = $this->httpClient();	
		$response = $client->request('POST',$this->endPoint(), ['form_params' => $metric]);
		return $this->handleResponse($response);
	}

	public function batchFire($metric_array)
	{
		$client = $this->httpClient();	
		$response = $client->request('POST',$this->batchEndPoint(), ['form_params' => $metric_array]);
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

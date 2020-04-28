<?php

namespace Turbo124\Collector\Collector;

use Turbo124\Collector\Collector;

class Generator
{
	private function endPoint($uri)
	{
		return config('collector.endpoint')."/{$uri}/add";
	}

	private function batchEndPoint($uri)
	{
		return config('collector.endpoint')."/{$uri}/batch";
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
		$data['metrics'] = $metric;

		$client = $this->httpClient();	
		$response = $client->request('POST',$this->endPoint($metric->type), ['form_params' => $data]);
		return $this->handleResponse($response);
	}

	public function batchFire($metric_array)
	{
		if(!is_array($metric_array))
			return;
		
		$data['metrics'] = $metric_array;

		$client = $this->httpClient();	
		$response = $client->request('POST',$this->batchEndPoint($metric_array[0]->type), ['form_params' => $data]);
		return $this->handleResponse($response);
	}


	private function handleResponse($response)
	{info(print_r($response,1));
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

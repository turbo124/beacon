<?php

namespace Turbo124\Collector\Collector;

use Turbo124\Collector\Collector;

class Generator
{
	/**
	 * The Collector Endpoint where
	 * we send our data to be injested
	 * @param  string $uri The collector type
	 * @return string      The full URL
	 */
	private function endPoint($uri)
	{
		return config('collector.endpoint')."/{$uri}/batch";
	}

	/**
	 * The API key used to communicate with 
	 * the collector.
	 * 
	 * @return string The alpha numeric string used to communicate with the API
	 */
	private function apiKey()
	{
		return config('collector.api_key');
	}

	/**
	 * The Http Client Instance
	 * @return Client The Guzzle client
	 */
	private function httpClient()
	{
		return new \GuzzleHttp\Client(['headers' => 
			[ 
		    'Authorization' => 'Bearer ' . $this->apiKey(),        
		    'Accept'        => 'application/json'
			]
		]);
	}

	/**
	 * Sends a single metric to the collector
	 * 
	 * @param  object $metric The user defined metric object
	 * @return void
	 */
	public function fire($metric)
	{
		$data['metrics'][] = $metric;

		$client = $this->httpClient();	
		$response = $client->request('POST',$this->endPoint($data['metrics'][0]->type), ['form_params' => $data]);
		return $this->handleResponse($response);
	}

	/**
	 * Sends a batch of metrics to the collector
	 * 
	 * @param  array $metric_array  Array of metric objects
	 * @return void              
	 */
	public function batchFire($metric_array)
	{
		if(!is_array($metric_array) || count($metric_array) == 0)
			return;
		
		$data['metrics'] = $metric_array;

		$client = $this->httpClient();	
		$response = $client->request('POST',$this->endPoint($metric_array[0]->type), ['form_params' => $data]);
		return $this->handleResponse($response);
	}

	/**
	 * Collector API response
	 * 
	 * @param  Response $response The API Collector response
	 * @return @todo need to handle failures gracefully here - the queues will retry if there is a failure so no need to respool
	 */
	private function handleResponse($response)
	{
		switch ($response->getStatusCode()) {
			case 200:
				# code...
				break;
			
			default:

				break;
		}
	}

}

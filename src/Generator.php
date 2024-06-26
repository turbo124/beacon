<?php

namespace Turbo124\Beacon;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use GuzzleHttp\{Client, HandlerStack, Middleware, RetryMiddleware};

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
		return config('beacon.endpoint') . "/{$uri}/batch";
	}

	private function alertEndPoint()
	{
		return config('beacon.endpoint') . "/alert";
	}
	/**
	 * The API key used to communicate with 
	 * the collector.
	 * 
	 * @return string The alpha numeric string used to communicate with the API
	 */
	private function apiKey()
	{
		return config('beacon.api_key');
	}

	/**
	 * The Http Client Instance
	 * @return Client The Guzzle client
	 */
	private function httpClient()
	{
		
		$maxRetries = 3;


		$decider = function (int $retries, RequestInterface $request, ResponseInterface $response = null) use ($maxRetries): bool {
			return
				$retries < $maxRetries
				&& null !== $response
				&& 429 === $response->getStatusCode();
		};

		$delay = function (int $retries, ResponseInterface $response): int {
			if (!$response->hasHeader('Retry-After')) {
				return RetryMiddleware::exponentialDelay($retries);
			}

			$retryAfter = $response->getHeaderLine('Retry-After');

			if (!is_numeric($retryAfter)) {
				$retryAfter = (new \DateTime($retryAfter))->getTimestamp() - time();
			}

			return (int) $retryAfter * 1000;
		};

		$stack = HandlerStack::create();
		$stack->push(Middleware::retry($decider, $delay));

		return new \GuzzleHttp\Client(['handler'  => $stack, 'headers' => 
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

		try {

			$client->request('POST',$this->endPoint($data['metrics'][0]->type), ['form_params' => $data]);

		} catch (RequestException $e) {

		}

	}

	public function alert($metric)
	{
		$data['metrics'][] = $metric;

		$client = $this->httpClient();	

		try {

			$client->request('POST',$this->alertEndPoint(), ['form_params' => $data]);

		} catch (RequestException $e) {

		}
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
		
		$client = $this->httpClient();	

		try {

            $batch_of = 40;
            $batch = array_chunk($metric_array, $batch_of);

            /* Concurrency ++ */
            foreach($batch as $key => $value) {	

            	$data['metrics'] = $value;

				$promises = [
				    $key => $client->requestAsync('POST',$this->endPoint($metric_array[0]->type), ['form_params' => $data])
				];

				$this->sendPromise($promises);

            }


		} catch (RequestException $e) {

			// info($e->getMessage());
		}
		
	}

	private function sendPromise($promises)
	{
		$responses = Promise\Utils::unwrap($promises);
	}

}

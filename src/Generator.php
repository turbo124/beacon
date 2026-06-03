<?php

namespace Turbo124\Beacon;

use GuzzleHttp\Promise;
use Psr\Http\Message\{RequestInterface, ResponseInterface};
use GuzzleHttp\{Client, HandlerStack, Middleware};

class Generator
{
    private ?Client $client = null;

    public function __construct(?Client $client = null)
    {
        $this->client = $client;
    }

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
        if ($this->client instanceof Client) {
            return $this->client;
        }

        $maxRetries = 3;


        $decider = function (int $retries, RequestInterface $request, ?ResponseInterface $response = null) use ($maxRetries): bool {
            return
                $retries < $maxRetries
                && null !== $response
                && 429 === $response->getStatusCode();
        };

        $delay = function (int $retries, ResponseInterface $response): int {
            if (!$response->hasHeader('Retry-After')) {
                // Exponential backoff in milliseconds. Inlined because
                // RetryMiddleware::exponentialDelay() is deprecated in
                // Guzzle 7.11 and removed in 8.0.
                return (int) (2 ** ($retries - 1)) * 1000;
            }

            $retryAfter = $response->getHeaderLine('Retry-After');

            if (!is_numeric($retryAfter)) {
                $retryAfter = (new \DateTime($retryAfter))->getTimestamp() - time();
            }

            return (int) $retryAfter * 1000;
        };

        $stack = HandlerStack::create();
        $stack->push(Middleware::retry($decider, $delay));

        return new \GuzzleHttp\Client(['handler'  => $stack, 'headers'
            => [
                'Authorization' => 'Bearer ' . $this->apiKey(),
                'Accept'        => 'application/json',
            ],
        ]);
    }

    /**
     * Sends a single metric to the collector
     *
     * @param  object $metric The user defined metric object
     *
     */
    public function fire($metric)
    {
        $data['metrics'][] = $metric;

        $endpoint = $this->endPoint($data['metrics'][0]->type);

        $data['metrics'] = $this->toArrays($data['metrics']);

        $client = $this->httpClient();

        try {

            $client->request('POST', $endpoint, ['form_params' => $data]);

        } catch (\Throwable $e) {
            // Telemetry must never break the host application.
        }

    }

    public function alert($metric)
    {
        $data['metrics'][] = $metric;

        $data['metrics'] = $this->toArrays($data['metrics']);

        $client = $this->httpClient();

        try {

            $client->request('POST', $this->alertEndPoint(), ['form_params' => $data]);

        } catch (\Throwable $e) {
            // Telemetry must never break the host application.
        }
    }

    /**
     * Sends a batch of metrics to the collector
     *
     * @param  ?array $metric_array  Array of metric objects
     *
     */
    public function batchFire($metric_array)
    {
        if (!is_array($metric_array) || count($metric_array) == 0) {
            return true;
        }

        $client = $this->httpClient();

        try {
            foreach ($this->groupMetricsByType($metric_array) as $type => $metrics) {
                $batch_of = 40;
                $batch = array_chunk($metrics, $batch_of);

                /* Concurrency ++ */
                foreach ($batch as $key => $value) {
                    $data['metrics'] = $this->toArrays($value);

                    $promises = [
                        $key => $client->requestAsync('POST', $this->endPoint($type), ['form_params' => $data]),
                    ];

                    $this->sendPromise($promises);

                }
            }

            return true;

        } catch (\Throwable $e) {
            // Telemetry must never break the host application. Also catches
            // the RejectionException that Promise\Utils::unwrap() throws when
            // an async request fails.
            return false;
        }

    }

    private function sendPromise($promises)
    {
        $responses = Promise\Utils::unwrap($promises);
    }

    /**
     * Normalises metric objects into plain arrays so they can be safely
     * serialized by Guzzle's form_params (http_build_query).
     *
     * Guzzle 7.11 deprecates - and 8.0 rejects - passing objects to
     * form_params. Metric classes expose only public properties, so an
     * (array) cast yields the exact same keys http_build_query previously
     * generated, keeping the wire format byte-for-byte identical.
     *
     * @param  array $metrics  Array of metric objects (or arrays)
     * @return array           The metrics as plain arrays
     */
    private function toArrays(array $metrics): array
    {
        return array_map(fn($metric) => is_object($metric) ? (array) $metric : $metric, $metrics);
    }

    private function groupMetricsByType(array $metrics): array
    {
        $grouped = [];

        foreach ($metrics as $metric) {
            $type = $this->metricType($metric);

            if ($type === null) {
                throw new \InvalidArgumentException('Metric type is required.');
            }

            $grouped[$type][] = $metric;
        }

        return $grouped;
    }

    private function metricType($metric): ?string
    {
        if (is_object($metric) && isset($metric->type)) {
            return (string) $metric->type;
        }

        if (is_array($metric) && isset($metric['type'])) {
            return (string) $metric['type'];
        }

        return null;
    }

}

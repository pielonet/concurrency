<?php
/* 
 * Parallel download with Guzzle 7
 * @ref https://docs.guzzlephp.org/en/stable/quickstart.html?highlight=concurrency#concurrent-requests
 * @ref https://docs.guzzlephp.org/en/stable/testing.html
 */

require "vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

// Start the server and queue a response
// Create a mock and queue two responses.
$responses = array_fill(0, 100, new Response(200, ['Content-Length' => 10000]));

$mock = new MockHandler($responses);

$handlerStack = HandlerStack::create($mock);
$client = new Client(['handler' => $handlerStack]);

//$client = new Client();

$requests = function ($total) {
    //$uri = 'http://127.0.0.1:8126/guzzle-server/perf';
    $uri = '/';
    for ($i = 0; $i < $total; $i++) {
        yield new Request('GET', $uri);
    }
};

$pool = new Pool($client, $requests(100), [
    'concurrency' => 5,
    'fulfilled' => function (Response $response, $index) {
        // this is delivered each successful response
    },
    'rejected' => function (RequestException $reason, $index) {
        // this is delivered each failed request
    },
]);

// Initiate the transfers and create a promise
$promise = $pool->promise();

// Force the pool of requests to complete.
$promise->wait();







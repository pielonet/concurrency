<?php
/* 
 * Parallel download with Guzzle 7
 * @ref https://docs.guzzlephp.org/en/stable/quickstart.html?highlight=concurrency#concurrent-requests
 */

include_once("config.php");

// Install Guzzle 7 if not yet installed
chdir(__DIR__);
shell_exec("composer install");

// Load Guzzle
require "vendor/autoload.php";

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;


function testConcurrency(int $concurrency, array $config) {
    $client = new Client();

    $requests = function (array $config) {
        foreach ($config['uris'] as $uri) {
            yield new Request('GET', $uri);
        }
    };

    $pool = new Pool($client, $requests($config), [
        'concurrency' => $concurrency,
        'fulfilled' => function (Response $response, $index) use ($config) {
            // this is delivered each successful response
            echo "downloaded {$config['uris'][$index]}, ";
        },
        'rejected' => function (RequestException $reason, $index) {
            // this is delivered each failed request
        },
    ]);

    // Initiate the transfers and create a promise
    $promise = $pool->promise();

    // Force the pool of requests to complete.
    $start = microtime(true);

    $promise->wait();

    $end = microtime(true);
    $duration = round($end - $start, 2);
    echo "\nConcurrency: $concurrency, Duration: {$duration}s\n";
}

testConcurrency(1, $config);
testConcurrency(2, $config);




<?php

/**
 * generic !
 * Parallel SSH as exec shell command and generic concurrency class
 */

include_once('config.php');
include_once('Pool.php');

use \Concurrency\Pool;

// Function that will be executed in each future (task)
$task = function (array $config, int $task_id, string $command) {
    include_once('SSH.php');
    $ssh_connection = new SSH($config);
    $start = microtime(true);
    $ssh_response = $ssh_connection->exec($command);
    $end = microtime(true);
    $duration = $end - $start;
    return [$task_id, $ssh_response, $duration];
};


// Generate tasks parameters
function generator(array $config) {
    extract($config);
    for ($task_id=1; $task_id <= $commands_count; $task_id++) {
        yield compact('config', 'task_id', 'command');
    }
};

$generator = generator($config);

// Function that will be executed each time a future completes,
// it receives the return values from the task as arguments
$fulfilled = function (int $task_id, string $ssh_response, float $duration) {
    echo "Task: $task_id, Response: $ssh_response\n";
    return compact('task_id', 'ssh_response', 'duration');
};

$pool = new Pool(
    concurrency: $config['concurrency'],
    task: $task,
    generator: $generator,
    fulfilled: $fulfilled
);

$pool->wait();

$requests = $pool->getResponse();
$requests_count = count($requests);
echo "$requests_count SSH requests.\n";
$column = array_column($requests, 'duration');
$total_duration = array_sum($column);
$average_request_duration = round($total_duration / $requests_count, 2);
echo "Average duration: {$average_request_duration}s\n";
echo "Total duration (without parallelism): " . round($total_duration, 2) . "s\n";

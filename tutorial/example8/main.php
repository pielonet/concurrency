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
    return compact('task_id', 'ssh_response', 'duration');
};


// Generate tasks parameters
function generator(array $config) {
    extract($config);
    for ($task_id=1; $task_id <= $commands_count; $task_id++) {
        yield [$config, $task_id, $command];
    }
};


function simulate(array $config, int $concurrency, \closure $task) {

    //Create generator
    $generator = generator($config);

    $pool = new Pool(
        concurrency: $concurrency,
        task: $task,
        generator: $generator,
    );

    $start_time = microtime(true);
    $requests = $pool->values();
    $duration = microtime(true) - $start_time;

    $requests_count = count($requests);
    echo "-------\n";
    echo "Concurrency: $concurrency\n";
    echo "$requests_count SSH requests have been run.\n";
    $column = array_column($requests, 'duration');
    $total_duration = array_sum($column);
    $average_request_duration = round($total_duration / $requests_count, 2);
    echo "Average task duration: {$average_request_duration}s\n";
    echo "Total duration (without parallelism): " . round($total_duration, 2) . "s\n";
    echo "Total duration (real): " . round($duration, 2) . "s\n";
    echo "Acceleration factor: " . round($total_duration / $duration, 2) . "\n";
}

simulate($config, 1, $task);
simulate($config, 5, $task);
simulate($config, 10, $task);
simulate($config, 20, $task);

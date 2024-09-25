<?php
/**
 * Mise en abyme : parallel executing a parallel script
 * 
 */

use Concurrency\Pool;

require "config.php";
require __DIR__ . "/../../../lib/Pool.php";

$tasks['singleQueue'] = function(array $config): array {
    return require "singleQueue.php";
};

$tasks['multipleQueues'] = function(array $config): array {
    return require "multipleQueues.php";
};

function generator(array $config) {
    for ($i=1 ; $i <= $config['iterations_count']; $i++) {
        yield [$config];
    }
}

foreach ($tasks as $name => $task) {

    //Create generator
    $generator = generator($config);

    $pool = new Pool(
        concurrency: $config['simulate_threads_count'],
        task: $task,
        generator: $generator,
    );

    $start_time = microtime(true);
    $results = $pool->values();
    $duration = microtime(true) - $start_time;

    echo "== $name ==\n";
    $count = count($results);
    echo "Number of iterations: $count\n";
    $column = array_column($results, 'clients_count');
    $average_clients_count = round(array_sum($column) / $count, 1);
    echo "Average clients count: $average_clients_count\n";

    $column = array_column($results, 'total_duration');
    $average_total_duration = round(array_sum($column) / $count);
    echo "Average total duration: {$average_total_duration}s\n";

    $column = array_column($results, 'max_wait_duration');
    $average_max_wait_duration = round(array_sum($column) / $count);
    echo "Average max wait duration: {$average_max_wait_duration}s\n";

    $column = array_column($results, 'average_wait_duration');
    $average_average = round(array_sum($column) / $count);
    echo "Average average wait duration: {$average_average}s\n";

    $simulation_duration = round($duration, 1);
    echo "Simulation duration: {$simulation_duration}s\n";
    echo "\n";
}


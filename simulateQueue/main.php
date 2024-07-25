<?php

include_once(__DIR__ . "/config.php");

print_r($config);

simulate('multipleQueue', $config);
simulate('singleQueue', $config);


// ==========================================

function simulate(string $queue_type, array $config) {
    $time_start = microtime(true);
    echo "\n== Queue type : $queue_type ==\n";
    if ($config['simulate_threads_count'] >= 2) {
        // Parallel processing

        $task = function (array $config, string $queue_type) {
            include_once __DIR__ . "/Queue.php";
            $queue = new Queue($config['write_log']);
            return $queue->$queue_type($config);
        };

        // Fill up threads with initial 'inactive' state
        $futures = array_fill(0, $config['simulate_threads_count'], null);

        $iteration = 1;
        while (! empty($futures)) {// Escape loop when all futures are destroyed
            // Loop through threads until all threads are finished
            foreach ($futures as $key => &$future) {
                if (is_null($future)) {
                    if ($iteration <= $config['iterations_count']) {
                        // Thread is inactive and there are still iterations to run : run something in the thread
                        $future = \parallel\run($task, [$config, $queue_type]);
                        $iteration++;
                        continue;
                    }

                    // Destroy thread in case all iterations are done
                    unset($futures[$key]);
                    continue;
                }

                //$thread is a future, test if task is done
                if ($future->done()) {
                    // Thread finished task. Get result value.
                    [$clients_count, $total_duration, $max_wait_duration, $average_wait_duration] = $future->value();
                    $results[] = compact('clients_count', 'total_duration', 'max_wait_duration', 'average_wait_duration');
                    // Set thread ready to run again
                    $future = null;
                }
            }
        }
    } else {
        // Sequential processing
        include_once __DIR__ . "/Queue.php";
        $queue = new Queue($config['write_log']);
        for ($n = 1; $n <= $config['iterations_count']; $n++) {
            [$clients_count, $total_duration, $max_wait_duration, $average_wait_duration] = $queue->$queue_type($config);;
            $results[] = compact('clients_count', 'total_duration', 'max_wait_duration', 'average_wait_duration');
        }
    }
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

    $time_end = microtime(true);
    $simulation_duration = $time_end - $time_start;
    echo "Simulation duration: {$simulation_duration}s\n";
}


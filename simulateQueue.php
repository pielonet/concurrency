<?php

$params = [
    'desks_count' => 4,
    'clients_count_max' => 120,
    'office_open_duration' => 1 * 60 * 60, // 3 hours

    'arrival_probability_check' => 'normal', // (linear|normal)
    // normal check
    'peak_time_minutes' => 30,
    'standard_deviation_minutes' => 20,
    // linear check

    'clients_min_desk_duration' => 30, // Minimum duration in seconds a client remains at desk
    'clients_max_desk_duration' => 200, // Maximum duration in seconds a client remains at desk

    //Parallel processing
    'simulate_in_parallel' => true,
    'simulate_threads_count' => 10, // Number of parallel threads

    'simulation_wait_microseconds' => 0,
    'write_log' => false,
];


simulate('multipleQueue', $params, 100);
simulate('singleQueue', $params, 100);


// ==========================================

function simulate(string $queue_type, array $params, int $iterations) {
    echo "== Queue type : $queue_type ==\n";
    print_r($params);
    if ($params['simulate_in_parallel']) {
        // Parallel processing

        $producer = function (array $params, string $queue_type) {
            include_once __DIR__ . "/lib/Queue.php";
            $queue = new Queue($params['write_log']);
            return $queue->$queue_type($params);
        };

        // Fill up threads with initial 'inactive' state
        $threads = array_fill(1, $params['simulate_threads_count'], []);

        $iteration = 1;
        while (true) {
            // Loop through threads until all threads are finished
            foreach ($threads as $thread_id => &$thread) {
                if (!isset($thread['future']) and $iteration < $iterations) {
                    // Thread is inactive and generator still has values : run something in the thread
                    $thread['future'] = \parallel\run($producer, [$params, $queue_type]);
                    $iteration++;
                } elseif (!isset($thread['future'])) {
                    // Destroy thread in case generator is closed
                    unset($threads[$thread_id]);
                } elseif ($thread['future']->done()) {
                    // Thread finished task. Get result value.
                    list($clients_count, $total_duration, $max_wait_duration, $average_wait_duration) = $thread['future']->value();
                    $results[] = compact('clients_count', 'total_duration', 'max_wait_duration', 'average_wait_duration');
                    // Set thread ready to run again
                    unset($thread['future']);
                }
            }

            // Escape loop when all threads are destroyed
            if (empty($threads)) break;
        }
    } else {
        // Sequential processing
        include_once __DIR__ . "/Queue.php";
        for ($n = 1; $n <= $iterations; $n++) {
            list($clients_count, $total_duration, $max_wait_duration, $average_wait_duration) = $queue_type($params);
            $results[] = compact('clients_count', 'total_duration', 'max_wait_duration', 'average_wait_duration');
        }
    }
    $count = count($results);
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
}


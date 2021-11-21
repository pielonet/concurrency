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

    'clients_min_desk_duration' => 30,
    'clients_max_desk_duration' => 200,
    'simulation_wait_microseconds' => 0,
    'logger' => false,
];


simulate('multipleQueue', $params, 100);

simulate('singleQueue', $params, 100);


// ==========================================

function simulate(callable $queue_type, array $params, int $iterations) {
    echo "== Queue type : $queue_type ==\n";
    print_r($params);
    for ($n = 1; $n <= $iterations; $n++) {
        list($clients_count, $total_duration, $max_wait_duration, $average_wait_duration) = $queue_type($params);
        $results[] = compact('clients_count', 'total_duration', 'max_wait_duration', 'average_wait_duration');
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

function generateLinearArrivalTimes($clients_count_max, $office_open_duration) {
   $client_arrive_probability = $clients_count_max / $office_open_duration;
   for ($time=0; $time <= $office_open_duration; $time++) {
        $p = mt_rand() / mt_getrandmax();
        if ($p <= $client_arrive_probability) $result[] = $time;
   }

   return $result;

}

/**
 * Generate arrival times using a normal distribution
 * Distribution is centered at $peak_time_minutes and has a standard deviation of $standard_deviation_minutes
 */
function generateNormalArrivalTimes(int $peak_time_minutes, int $standard_deviation_minutes, int $clients_count_max): array {
    for ($i=1; $i <= $clients_count_max; $i++) {
        $result[] = round(\stats_rand_gen_normal($peak_time_minutes*60, $standard_deviation_minutes*60)) . "\n";
    }

    return $result;
}


function logger(string $text) {
    if ($GLOBALS['params']['logger']) echo $text;
}

function singleQueue(array $params) {

    extract($params);

    $time = 0;
    $queue = [];
    $clients = [];
    $clients_entered_count = 0;

    $desks = array_fill(1, $desks_count, null);

    if ($arrival_probability_check == 'normal') {
        $arrival_times = generateNormalArrivalTimes($peak_time_minutes, $standard_deviation_minutes, $clients_count_max);
    } else {
        $arrival_times = generateLinearArrivalTimes($clients_count_max, $office_open_duration);
    }

    while (true) {
        logger("Time: {$time}s\n");
        //$client_enters_queue = (checkLinearArrivalTimes($client_arrive_probability) and $time < $office_open_duration) ? true : false;
        $client_enters_queue = (in_array($time, $arrival_times) and $time < $office_open_duration) ? true : false;
        if ($client_enters_queue) {
            $clients_entered_count++;
            logger("ClientId $clients_entered_count enters queue. ");
            $clients[$clients_entered_count]['enters_queue_time'] = $time;
            $queue[] = $clients_entered_count;
            logger(count($queue) . " client(s) in queue\n");
        }
        foreach ($desks as $desk_id => &$client_id) {
            if ($client_id) {
                $client = $clients[$client_id];
                if ($time == $client['leaves_desk_time']) {
                    logger("ClientId $client_id leaves deskId $desk_id\n");
                    // Empty desk
                    $client_id = null;
                }
            }
            if (is_null($client_id)) {
                $new_client_id = array_shift($queue);
                if (!is_null($new_client_id)) {
                    // Update desk
                    $client_id = $new_client_id;
                    // Update client
                    $client = &$clients[$new_client_id];
                    $client['queue_wait_duration'] = $time - $client['enters_queue_time'];
                    $client['desk_visited'] = $desk_id;
                    $desk_duration = rand($clients_min_desk_duration, $clients_max_desk_duration);
                    $client['leaves_desk_time'] = $time + $desk_duration;
                    logger("ClientId $client_id enters deskId $desk_id for {$desk_duration}s. ");
                    logger("Client waited {$client['queue_wait_duration']}s in queue. ");
                    logger(count($queue) . " client(s) in queue\n");
                    unset($client);
                }
            }
        }
        unset($client_id);
        $time++;
        usleep($simulation_wait_microseconds);

        // End simulation
        //if (count($clients) == $clients_count_max) {
        if ($time >= $office_open_duration) {
            foreach($desks as $client_id) {
                if (!is_null($client_id)) continue 2;
            }
            break;
        }
        //}
    }

    // Compute statistics

    $clients_count = count($clients);
    logger("Clients count: $clients_count\n");
    $column = array_column($clients, 'queue_wait_duration');
    $max_wait_duration = max($column);
    logger("Max wait duration: {$max_wait_duration}s\n");
    $average_wait_duration = round(array_sum($column) / $clients_count);
    logger("Average wait duration: {$average_wait_duration}s\n");

    return [$clients_count, $time, $max_wait_duration, $average_wait_duration];
}

function multipleQueue(array $params) {

    extract($params);

    $time = 0;
    $clients = [];
    $clients_entered_count = 0;

    $desks = array_fill(1, $desks_count, null);
    $queues = array_fill(1, $desks_count, []);

    if ($arrival_probability_check == 'normal') {
        $arrival_times = generateNormalArrivalTimes($peak_time_minutes, $standard_deviation_minutes, $clients_count_max);
    } else {
        $arrival_times = generateLinearArrivalTimes($clients_count_max, $office_open_duration);
    }
    
    while (true) {
        logger("Time: {$time}s\n");
        //$client_enters_queue = (checkWithLinearProbability($client_arrive_probability) and $time < $office_open_duration) ? true : false;
        $client_enters_queue = (in_array($time, $arrival_times) and $time < $office_open_duration) ? true : false;
        if ($client_enters_queue) {
            $clients_entered_count++;
            $clients[$clients_entered_count]['enters_queue_time'] = $time;
            // Select queue with least number of clients
            foreach ($desks as $desk_id => $client_id) {
                $queues_counts[$desk_id] = (is_null($client_id) ? 0 : 1) + count($queues[$desk_id]);
            }
            $queue_id = current(array_keys($queues_counts, min($queues_counts)));
            $queues[$queue_id][] = $clients_entered_count;
            logger("ClientId $clients_entered_count enters QueueId $queue_id. Queue length is " . count($queues[$queue_id]) . ".\n");
        }
        foreach ($desks as $desk_id => &$client_id) {
            if ($client_id) {
                $client = $clients[$client_id];
                if ($time == $client['leaves_desk_time']) {
                    logger("ClientId $client_id leaves deskId $desk_id\n");
                    // Empty desk
                    $client_id = null;
                }
            }
            if (is_null($client_id)) {
                $new_client_id = array_shift($queues[$desk_id]);
                if (!is_null($new_client_id)) {
                    // Update desk
                    $client_id = $new_client_id;
                    // Update client
                    $client = &$clients[$new_client_id];
                    $client['queue_wait_duration'] = $time - $client['enters_queue_time'];
                    $client['desk_visited'] = $desk_id;
                    $desk_duration = rand($clients_min_desk_duration, $clients_max_desk_duration);
                    $client['leaves_desk_time'] = $time + $desk_duration;
                    logger("ClientId $client_id enters deskId $desk_id for {$desk_duration}s. ");
                    logger("Client waited {$client['queue_wait_duration']}s in queue. ");
                    logger("Queue length is " . count($queues[$desk_id]) . ".\n");
                    unset($client);
                }
            }
        }
        unset($client_id);
        $time++;
        usleep($simulation_wait_microseconds);

        // End simulation
        //if (count($clients) == $clients_count_max) {
        if ($time >= $office_open_duration) {
            foreach($desks as $client_id) {
                if (!is_null($client_id)) continue 2;
            }
            break;
        }
        //}
    }

    // Compute statistics

    $clients_count = count($clients);
    logger("Clients count: $clients_count\n");
    $column = array_column($clients, 'queue_wait_duration');
    $max_wait_duration = max($column);
    logger("Max wait duration: {$max_wait_duration}s\n");
    $average_wait_duration = round(array_sum($column) / $clients_count);
    logger("Average wait duration: {$average_wait_duration}s\n");

    return [$clients_count, $time, $max_wait_duration, $average_wait_duration];
}

<?php

/**
 * Simulate multiple queues and multiple desks with parallel threads
 * 
 */

if (! isset($config)) {
    require_once "config.php";
 }

require_once "Utils.php";

Utils::setConfig($config);

use \parallel\Channel;

// Create new buffered channel for each queue
for ($queue_id=0; $queue_id < $config['desks_count']; $queue_id++) {
    $queues_channels[] = new Channel(Channel::Infinite);
}
$stats_channel = new Channel(Channel::Infinite);

if ($config['arrival_probability_law'] == 'normal') {
    $arrival_times = Utils::generateNormalArrivalTimes($config['peak_time_minutes'], $config['standard_deviation_minutes'], $config['clients_count_max']);
} else {
    $arrival_times = Utils::generateLinearArrivalTimes($config['clients_count_max'], $config['office_open_duration_seconds']);
}

$duration_seconds = 0;
$client_id = 0;

$stats = \parallel\run(
    function(Channel $stats_channel, array $queues_channels, array $config): array {

        require_once "Utils.php";

        Utils::setConfig($config);

        $clients = [];
        $queues = array_fill(0, $config['desks_count'], []);
        $desks = array_fill(0, $config['desks_count'], null);
        $event = [];

        try {
            while(true) {
                $event = $stats_channel->recv();
                extract($event['data']);
                $iteration_time = microtime(true);
                $duration = $iteration_time - $start_time;
                $duration_seconds = round($duration * (1000000 / $config['simulation_wait_microseconds']));

                switch ($event['action']) {
                    case 'simulation_starts':
                        Utils::logger("Time: {$duration_seconds}s, Simulation starts\n");
                        break;

                    case 'client_enters_queue':
                        // Select queue with least number of clients
                        foreach ($desks as $desk_id => $my_client_id) {
                            $queues_counts[$desk_id] = (is_null($my_client_id) ? 0 : 1) + count($queues[$desk_id]);
                        }
                        $queue_id = current(array_keys($queues_counts, min($queues_counts)));
                        Utils::logger("Time: {$duration_seconds}s, Client $client_id enters queue $queue_id.\n");
                        $queues_channels[$queue_id]->send($client_id);
                        $clients[$client_id]['queue_enter_time'] = $duration_seconds;
                        $queues[$queue_id][] = $client_id;
                        break;

                    case 'client_enters_desk':
                        Utils::logger("Time: {$duration_seconds}s, Client $client_id enters desk $desk_id for {$desk_duration}s\n");
                        $clients[$client_id]['desk_enter_time'] = $duration_seconds;
                        $clients[$client_id]['queue_wait_duration'] = $duration_seconds - $clients[$client_id]['queue_enter_time'];
                        $clients[$client_id]['desk_duration'] = $desk_duration;
                        $clients[$client_id]['desk_visited'] = $desk_id;
                        array_shift($queues[$desk_id]);
                        $desks[$desk_id] = $client_id;
                        break;

                    case 'client_leaves_desk':
                        Utils::logger("Time: {$duration_seconds}s, Client $client_id leaves desk $desk_id\n");
                        $desks[$desk_id] = null;
                        break;
                        
                    case 'desk_closes':
                        Utils::logger("Time: {$duration_seconds}s, Desk $desk_id closes.\n");
                        break;
                }
                if ($config['display_simulation']) Utils::multipleQueueToTxt($queues, $desks);
            }

        } catch(\parallel\Channel\Error\Closed $e) {
            return $clients;
        }

    },
    [$stats_channel, $queues_channels, $config]
);

// Launch desks
for ($desk_id = 0; $desk_id < $config['desks_count']; $desk_id++ ) {
    $desks[] = \parallel\run(
        function (int $desk_id, Channel $queue, Channel $stats_channel, array $config): void {
            try {
                while(true) {
                    $client_id = $queue->recv();
                    $desk_duration = rand($config['clients_min_desk_duration_seconds'], $config['clients_max_desk_duration_seconds']);
                    $stats_channel->send(['action' => 'client_enters_desk', 'data' => compact('client_id', 'desk_id', 'desk_duration')]);
                    // Wait for $desk_duration virtual seconds have elapsed
                    usleep($desk_duration * $config['simulation_wait_microseconds']);
                    $stats_channel->send(['action' => 'client_leaves_desk', 'data' => compact('client_id', 'desk_id')]);
                }
            } catch(\parallel\Channel\Error\Closed $e) {
                $stats_channel->send(['action' => 'desk_closes', 'data' => compact('desk_id')]);
                return;
            }
        },
        [$desk_id, $queues_channels[$desk_id], $stats_channel, $config]
    );
}

$start_time = microtime(true);
$stats_channel->send(['action' => 'simulation_starts', 'data' => compact('start_time')]);

while($duration_seconds < $config['office_open_duration_seconds']) {
    $iteration_start_time = microtime(true);
    $duration = $iteration_start_time - $start_time;
    $old_duration_seconds = $duration_seconds;
    $duration_seconds = round($duration * (1000000 / $config['simulation_wait_microseconds']));
    $client_enters_queue = false;
    if ($duration_seconds == $old_duration_seconds + 2) {
        $client_enters_queue = (in_array($duration_seconds - 1, $arrival_times) or in_array($duration_seconds, $arrival_times));
    } else {
        $client_enters_queue = in_array($duration_seconds, $arrival_times);
    }
    if ($client_enters_queue) {
        $client_id++;
        $stats_channel->send(['action' => 'client_enters_queue', 'data' => compact('client_id')]);
    }
    // Wait until a single "virtual" second has elapsed
    usleep($config['simulation_wait_microseconds']);
}

// Close all queue channels
foreach ($queues_channels as $channel) {
    $channel->close();
}

// waiting until all "desk" threads are done
do {
    foreach ($desks as $desk) {
        if (! $desk->done()) { continue 2; }
    }
    break;
} while (true);


$stats_channel->close();
$clients = $stats->value();

$total_duration = round((microtime(true) - $start_time) * (1000000 / $config['simulation_wait_microseconds']));

// Compute statistics

$clients_count = count($clients);
Utils::logger("Clients count: $clients_count\n");
$column = array_column($clients, 'queue_wait_duration');
$max_wait_duration = max($column);
Utils::logger("Max wait duration: {$max_wait_duration}s\n");
$average_wait_duration = round(array_sum($column) / $clients_count);
Utils::logger("Average wait duration: {$average_wait_duration}s". PHP_EOL);

$statistics = compact('clients_count', 'total_duration', 'max_wait_duration', 'average_wait_duration');

$simulation_duration = round(microtime(true) - $start_time, 2);
Utils::logger("Simulation duration: {$simulation_duration}s\n");












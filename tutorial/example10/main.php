<?php

/**
 * Simulate single queue and multiple desks with parallel threads
 * 
 */

if (! isset($config)) {
    require_once "config.php";
 }

require_once "Utils.php";

Utils::setConfig($config);

use \parallel\Channel;

// Create new buffered channel
$main_queue = new Channel(Channel::Infinite);
$stats_channel = new Channel(Channel::Infinite);

if ($config['arrival_probability_law'] == 'normal') {
    $arrival_times = Utils::generateNormalArrivalTimes($config['peak_time_minutes'], $config['standard_deviation_minutes'], $config['clients_count_max']);
} else {
    $arrival_times = Utils::generateLinearArrivalTimes($config['clients_count_max'], $config['office_open_duration_seconds']);
}

$duration_seconds = 0;
$client_id = 0;



$stats = \parallel\run(
    function(Channel $stats_channel, array $config): array {

        require_once "Utils.php";

        Utils::setConfig($config);

        $clients = [];
        $queue = [];
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
                        Utils::logger("Time: {$duration_seconds}s, Client $client_id enters queue.\n");
                        $clients[$client_id]['queue_enter_time'] = $duration_seconds;
                        $queue[] = $client_id;
                        break;

                    case 'client_enters_desk':
                        Utils::logger("Time: {$duration_seconds}s, Client $client_id enters desk $desk_id for {$desk_duration}s\n");
                        $clients[$client_id]['desk_enter_time'] = $duration_seconds;
                        $clients[$client_id]['queue_wait_duration'] = $duration_seconds - $clients[$client_id]['queue_enter_time'];
                        $clients[$client_id]['desk_duration'] = $desk_duration;
                        $clients[$client_id]['desk_visited'] = $desk_id;
                        array_shift($queue);
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
                if ($config['display_simulation']) Utils::singleQueueToTxt($queue, $desks);
            }

        } catch(\parallel\Channel\Error\Closed $e) {
            return $clients;
        }

    },
    [$stats_channel, $config]
);

// Launch desks
for ($desk_id = 0; $desk_id < $config['desks_count']; $desk_id++ ) {
    $desks[] = \parallel\run(
        function (int $desk_id, Channel $main_queue, Channel $stats_channel, array $config): void {
            try {
                while(true) {
                    $client_id = $main_queue->recv();
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
        [$desk_id, $main_queue, $stats_channel, $config]
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
        $main_queue->send($client_id);
    }
    // Wait until a single "virtual" second has elapsed
    usleep($config['simulation_wait_microseconds']);
}

$main_queue->close();

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












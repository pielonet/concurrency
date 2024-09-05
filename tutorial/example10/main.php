<?php

/**
 * Simulate single queue and multiple desks with parallel threads
 * 
 */

require_once "config.php";
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
    function(Channel $stats_channel) {
        require_once "config.php";
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
                switch ($event['action']) {
                    case 'client_enters_queue':
                        $clients[$client_id]['queue_enter_time'] = $duration_seconds;
                        $queue[] = $client_id;
                        break;

                    case 'client_enters_desk':
                        $clients[$client_id]['queue_wait_duration'] = $duration_seconds - $clients[$client_id]['queue_enter_time'];
                        $clients[$client_id]['desk_duration'] = $desk_duration;
                        $clients[$client_id]['desk_visited'] = $desk_id;
                        array_shift($queue);
                        $desks[$desk_id] = $client_id;
                        break;
                    case 'client_leaves_desk':
                        $desks[$desk_id] = null;
                        break;
                }
                Utils::singleQueueToTxt($queue, $desks);
            }

        } catch(\parallel\Channel\Error\Closed $e) {
            return $clients;
        }

    },
    [$stats_channel]
);


$start_time = microtime(true);

for ($desk_id = 0; $desk_id < $config['desks_count']; $desk_id++ ) {
    $desks[] = \parallel\run(
        function (int $desk_id, Channel $main_queue, Channel $stats_channel, float $start_time): void {
            require_once "config.php";
            require_once "Utils.php";

            Utils::setConfig($config);

            try {
                while(true) {
                    [$client_id] = $main_queue->recv();
                    $duration = microtime(true) - $start_time;
                    $duration_seconds = round($duration * $config['time_acceleration_factor']);
                    $desk_duration = rand($config['clients_min_desk_duration_seconds'], $config['clients_max_desk_duration_seconds']);
                    $stats_channel->send(['action' => 'client_enters_desk', 'data' => compact('duration_seconds', 'client_id', 'desk_id', 'desk_duration')]);
                    Utils::logger("Time: {$duration_seconds}s, Client $client_id enters desk $desk_id for {$desk_duration}s\n");
 
                    // Wait for $desk_duration virtual seconds have elapsed
                    Utils::sleep($start_time, $duration_seconds, $desk_duration);

                    $duration_seconds = $duration_seconds + $desk_duration;
                    $stats_channel->send(['action' => 'client_leaves_desk', 'data' => compact('duration_seconds', 'client_id', 'desk_id')]);
                    Utils::logger("Time: {$duration_seconds}s, Client $client_id leaves desk $desk_id\n");
                }
            } catch(\parallel\Channel\Error\Closed $e) {
                $duration = microtime(true) - $start_time;
                $duration_seconds = round($duration * $config['time_acceleration_factor']);
                Utils::logger("Time: {$duration_seconds}s, Desk $desk_id closes.\n");
                return;
            }
        },
        [$desk_id, $main_queue, $stats_channel, $start_time]
    );
}

while($duration_seconds < $config['office_open_duration_seconds']) {
    //Utils::logger("Time: {$duration_seconds}s\n");
    $client_enters_queue = in_array($duration_seconds, $arrival_times);
    if ($client_enters_queue) {
        $client_id++;
        Utils::logger("Time: {$duration_seconds}s, Client $client_id enters queue.\n");
        $main_queue->send([$client_id]);
        $stats_channel->send(['action' => 'client_enters_queue', 'data' => compact('duration_seconds', 'client_id')]);
    }

    // Wait until a single "virtual" second has elapsed
    Utils::sleep($start_time, $duration_seconds, 1);
    $duration_seconds++;
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

$simulation_duration = round(microtime(true) - $start_time, 2);
echo "Simulation duration: {$simulation_duration}s\n";












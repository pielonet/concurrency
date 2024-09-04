<?php

/**
 * pong...zzz!
 * Run two simple tasks in parallel and synchronize them with a channel
 * 
 * parallel\Channel(int $capacity): Buffered channel
 * Creates a buffered channel for communication between tasks
 * @ref https://www.php.net/manual/en/class.parallel-channel.php
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

$start_time = microtime(true);

for($desk_id = 0; $desk_id < $config['desks_count']; $desk_id++ ) {
    $desks[] = \parallel\run(
        function($desk_id, Channel $main_queue, Channel $stats_channel, float $start_time) {
            require_once "config.php";
            require_once "Utils.php";

            Utils::setConfig($config);

            try {
                while(true) {
                    [$client_id] = $main_queue->recv();
                    $duration = microtime(true) - $start_time;
                    $duration_seconds = round($duration * $config['time_acceleration_factor']);
                    $desk_duration = rand($config['clients_min_desk_duration_seconds'], $config['clients_max_desk_duration_seconds']);
                    //$stats_channel->send([$duration_seconds, $client_id, $desk_id, $desk_duration]);
                    Utils::logger("Time: {$duration_seconds}s, Client $client_id enters desk $desk_id for {$desk_duration}s\n");
 
                    // Wait for $desk_duration virtual seconds have elapsed
                    Utils::sleep($start_time, $duration_seconds, $desk_duration);

                    $duration_seconds = $duration_seconds + $desk_duration;
                    Utils::logger("Time: {$duration_seconds}s, Client $client_id leaves desk $desk_id\n");
                }
            } catch(\parallel\Channel\Error\Closed $e) {
                Utils::logger("Main queue is closed. Close Desk $desk_id as well.\n");
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
        //$stats_channel->send([$duration_seconds, $client_id]);
    }

    // Wait until a single "virtual" second has elapsed
    Utils::sleep($start_time, $duration_seconds, 1);
    $duration_seconds++;
}

$main_queue->close();





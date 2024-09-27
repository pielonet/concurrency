<?php
/**
 * Simulate a single queue for a single desk
 * 
 * Use a channel to simulate the desk
 */

use \parallel\Channel;

$virtual_second = 1000; // Duration of a virtual second in microseconds
$simulated_seconds = 3600;
$min_desk_duration = 60; // Minimum duration in seconds a client spends at the desk
$max_desk_duration = 180; // Maximum duration in seconds a client spends at the desk
$client_enters_probability = 0.01;

function clientEntersQueue($client_enters_probability): bool {
    $q = mt_rand() / mt_getrandmax();
    return ($q < $client_enters_probability);
}

$channel = new Channel(Channel::Infinite);

$desk = \parallel\run(function($channel, $virtual_second, /* REPLACE ME */) {
    $initial_time = microtime(true);
    try {
        while (true) {
            $client_number = /* REPLACE ME */;
            $real_duration = microtime(true) - $initial_time;
            $simulated_duration = round($real_duration * (1000000 / $virtual_second));
            $desk_duration = rand($min_desk_duration, $max_desk_duration);
            echo "Time: $simulated_duration - Client $client_number enters desk for {$desk_duration}s\n";
            usleep(/* REPLACE ME */);
            $simulated_duration += $desk_duration;
            echo "Time: $simulated_duration - Client $client_number leaves desk\n";
        }
    } catch (/* REPLACE ME */ $e) {
        return;
    }
}, [$channel, $virtual_second, /* REPLACE ME */]);


$simulated_duration = 0;
$client_number = 0;

$initial_time = microtime(true);
while ($simulated_duration <= $simulated_seconds) {
    $real_duration = microtime(true) - $initial_time;
    $simulated_duration = round($real_duration * (1000000 / $virtual_second));

    if (clientEntersQueue($client_enters_probability)) {
        $client_number++;
        echo "Time: $simulated_duration - Client $client_number enters queue.\n";
        $channel->send($client_number);
    }

    usleep($virtual_second);
}

$channel->close();

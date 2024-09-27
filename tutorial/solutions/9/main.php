<?php
/**
 * Simulate single queue and 4 desks
 */

use \parallel\Channel;

$virtual_second = 1000; // Duration of a virtual second in microseconds
$desks_count = 4;
$simulated_seconds = 3600;
$min_desk_duration = 60; // Minimum duration in seconds a client spends at the desk
$max_desk_duration = 180; // Maximum duration in seconds a client spends at the desk
$client_enters_probability = 0.01;

function clientEntersQueue($client_enters_probability): bool {
    $q = mt_rand() / mt_getrandmax();
    return ($q < $client_enters_probability);
}

$channel = new Channel(Channel::Infinite);

for ($desk_number=1; $desk_number <= $desks_count; $desk_number++) {
    $desks[] = \parallel\run(function($channel, $virtual_second, $min_desk_duration, $max_desk_duration, $desk_number) {
        $initial_time = microtime(true);
        try {
            while (true) {
                $client_number = $channel->recv();
                $real_duration = microtime(true) - $initial_time;
                $simulated_duration = round($real_duration * (1000000 / $virtual_second));
                $desk_duration = rand($min_desk_duration, $max_desk_duration);
                echo "Time: $simulated_duration - Client $client_number enters desk $desk_number for {$desk_duration}s\n";
                usleep($desk_duration * $virtual_second);
                $simulated_duration += $desk_duration;
                echo "Time: $simulated_duration - Client $client_number leaves desk $desk_number\n";
            }
        } catch (\parallel\Channel\Error\closed $e) {
            return;
        }
    }, [$channel, $virtual_second, $min_desk_duration, $max_desk_duration, $desk_number]);
}


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
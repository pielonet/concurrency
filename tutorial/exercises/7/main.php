<?php
/**
 * Simulate single queue
 * and calculate accelerated time
 * 
 * Find the right caculation for the simulated duration
 */


$virtual_second = 1000; // Duration of a virtual second in microseconds
$simulated_seconds = 3600;
$client_enters_probability = 0.01;

$simulated_duration = 0;
$client_number = 0;

$initial_time = microtime(true);
while ($simulated_duration <= $simulated_seconds) {
    $real_duration = microtime(true) - $initial_time;
    $simulated_duration = round(/* REPLACE ME */);
 
    $q = mt_rand() / mt_getrandmax();
    if ($q < $client_enters_probability) {
        $client_number++;
        echo "Time: $simulated_duration - Client $client_number enters queue.\n";
    }

    usleep($virtual_second);
}

$channel->close();

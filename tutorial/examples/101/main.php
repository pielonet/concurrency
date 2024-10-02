<?php
/**
 * Prime sieve with daisy-chain filter processes
 * 
 * Inspired by : https://go.dev/
 * Explanation : https://stackoverflow.com/questions/52179456/can-i-get-some-help-to-reason-about-the-concurrent-prime-sieve-example
 */

use \parallel\Channel;

// Send the sequence 2, 3, 4, ... to channel '$channel'.
$generate = function (Channel $channel) {
    try {
        for ($i=2 ; ; $i++) {
            $channel->send($i);
        }
    } catch(Channel\Error\Closed) {
        die;
    }
};


// Copy the values from channel 'in' to channel 'out',
// removing those divisible by 'prime'.
$filter = function (Channel $in, Channel $out, int $prime) {
    try {
        while (true) {
            $i = $in->recv(); // Receive value from 'in'.
            if ($i%$prime != 0) {
                $out->send($i); // Send 'i' to 'out'.
            }
        }
    } catch(Channel\Error\Closed) {
        die;
    }
};

// The prime sieve: Daisy-chain Filter processes.

// Create an unbuffered channel
$channel = new Channel();
$channel1 = $channel;
$channels = [];

// Launch Generate thread
\parallel\run($generate, [$channel]);


$primes_count = 100;

for ($i = 0; $i < $primes_count; $i++) {
    $prime = $channel->recv();
    echo "$prime\n";
    $channels[$i] = new Channel();
    \parallel\run($filter, [$channel, $channels[$i], $prime]);
    $channel = $channels[$i];
}

// All created channels need to be closed for each alive threads
// (generator and filters) to terminate properly and the program to exit
$channel1->close();
for ($i = 0; $i < $primes_count; $i++) {
    $channels[$i]->close();
}

<?php

/**
 * pong...zzz!
 * Run two simple tasks in parallel and synchronize them with a channel
 * 
 * parallel\Channel(int $capacity): Buffered channel
 * Creates a buffered channel for communication between tasks
 * @ref https://www.php.net/manual/en/class.parallel-channel.php
 */

use \parallel\Runtime;

// Create new buffered channel of size 2
$channel = new \parallel\Channel(2);

$runtime1 = new Runtime();
$runtime1->run(
    function($channel) {
        for ($i=1; $i<=8; $i++) {
            $my_sleep_time = rand(1, 5);
            $other_sleep_time = rand(1, 5);
            $channel->send($other_sleep_time);
            sleep($my_sleep_time);
        }
        echo "I finished sleeping. Closing channel" . PHP_EOL;
        $channel->close();
    },
    [$channel]
);

$runtime2 = new Runtime();
$runtime2->run(
    function($channel) {
        try {
            while(true) {
                $my_sleep_time = $channel->recv();
                sleep($my_sleep_time);
            }
        } catch(\parallel\Channel\Error\Closed $e) {
            return;
        }
    },
    [$channel]
);

$runtime1->close();
$runtime2->close();


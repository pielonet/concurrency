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

echo "zzz... " . PHP_EOL;

// Create new buffered channel
$channel = new \parallel\Channel(2);

$runtime1 = new Runtime();
$runtime1->run(
    function($channel) {
        $snaps_count = rand (8, 12);
        echo "Number of snaps: $snaps_count" . PHP_EOL;
        for ($i=1; $i<=$snaps_count; $i++) {
            $my_sleep_time = rand(1, 5);
            $other_sleep_time = rand(1, 5);

            echo "Send sleep time to buffer" . PHP_EOL;
            $start = microtime(true);
            $channel->send($other_sleep_time);
            $wait_time = microtime(true) - $start;
            if ($wait_time > .1) {
                echo "Buffer was full. I waited " . round($wait_time) . "s" . PHP_EOL;
            }

            echo "I sleep for {$my_sleep_time}s" . PHP_EOL;
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
                $start = microtime(true);
                $my_sleep_time = $channel->recv();
                $wait_time = microtime(true) - $start;
                if ($wait_time > .1) {
                    echo "Buffer was empty. Other waited " . round($wait_time) . "s" . PHP_EOL;
                }
                echo "Other sleeps for {$my_sleep_time}s" . PHP_EOL;
                sleep($my_sleep_time);
            }
        } catch(\parallel\Channel\Error\Closed $e) {
            echo "Channel is closed. Other dies.";
            die;
        }
    },
    [$channel]
);



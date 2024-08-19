<?php

/**
 * Bzz reloaded!
 * Run two simple tasks in parallel and synchronize them with a channel
 * 
 * parallel\Channel(int $capacity): Buffered channel
 * Creates a buffered channel for communication between tasks
 * @ref https://www.php.net/manual/en/class.parallel-channel.php
 */

 echo "zzz... " . PHP_EOL;

// Create new buffered channel
$channel = new \parallel\Channel(2);

\parallel\run(
    function($channel) {
        $snaps_count = rand (8, 12);
        echo "Number of snaps: $snaps_count" . PHP_EOL;
        for ($i=1; $i<=$snaps_count; $i++) {
            $my_sleep_time = rand(1, 3);
            // Second task is slower
            $other_sleep_time = rand(3, 5);

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

\parallel\run(
    function($channel) {
        try {
            while(true) {
                $my_sleep_time = $channel->recv();
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



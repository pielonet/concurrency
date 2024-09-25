<?php

/**
 * pong...zzz!
 * Run two simple tasks in parallel and synchronize them with a channel
 * 
 * parallel\Channel(int $capacity): Buffered channel
 * Creates a buffered channel for communication between tasks
 * @ref https://www.php.net/manual/en/class.parallel-channel.php
 */

// Create new buffered channel of size 1
$channel = new \parallel\Channel(1);

\parallel\run(
    function($channel) {
        $channel->send(2);
        sleep(1);
        $my_sleep_time = $channel->recv();
        sleep($my_sleep_time);
        echo "here";
        $channel->close();
    },
    [$channel]
);

\parallel\run(
    function($channel) {
        $my_sleep_time = $channel->recv();
        sleep($my_sleep_time);
        echo "there";
        $channel->send(1);
    },
    [$channel]
);


echo "zzz";

for ($i=1; $i<=5; $i++) {
    sleep(1);
    echo ".";
}

// Result : zzz.there.here...

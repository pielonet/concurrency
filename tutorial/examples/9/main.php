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
        $channel->send("there");
        sleep(1);
        $message = $channel->recv();
        echo $message;
        $channel->close();
    },
    [$channel]
);

\parallel\run(
    function($channel) {
        $message = $channel->recv();
        echo $message;
        sleep(2);
        $channel->send("here");
    },
    [$channel]
);


echo "zzz";

for ($i=1; $i<=5; $i++) {
    echo ".";
    sleep(1);   
}

// Result : zzz.there..here..

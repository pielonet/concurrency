<?php
/**
 * - Create two parallel tasks that exchange "ping"
 *   and "pong" messages every second
 *   over one channel of size 1, indefinitely, 
 * - DO NOT FORGET to close the channel in the main thread
 *   after 10 seconds to interrupt the script
 * 
 * - Modify your script to smoothly handle the error that arises
 */

$channel = new \parallel\Channel(1);

\parallel\run(function ($channel) {
        try {
            while (true) {
                $channel->send("ping ");
                usleep(1000);
                $message = $channel->recv();
                echo $message;
                sleep(1);
            }
        } catch (\parallel\Channel\Error\Closed $e) {
            die;
        }
    },
    [$channel]
);

\parallel\run(function ($channel) {
        try {
            while (true) {
                $message = $channel->recv();
                echo $message;
                sleep(1);
                $channel->send("pong ");
                usleep(1000);
            }
        } catch (\parallel\Channel\Error\Closed $e) {
            die;
        }
    },
    [$channel]
);

sleep(10);
$channel->close();

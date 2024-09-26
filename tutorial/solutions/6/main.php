<?php

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

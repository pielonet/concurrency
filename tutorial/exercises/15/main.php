<?php
/**
 * Use an event-loop instead of a try/catch statement to handle the close event
 */

 use \parallel\Channel;


$channel = new Channel();

$task = static function(Channel $channel) {
    try {
        while(true) {
            $sleep_time = $channel->recv();
            sleep($sleep_time);
            echo ".";
        }
    } catch (Channel\Error\Closed) {
        echo "bye\n";
        die;
    }
};

\parallel\run($task, [$channel]);

for ($i=1; $i < 5; $i++) {
    $channel->send(random_int(1,2));
}

$channel->close();
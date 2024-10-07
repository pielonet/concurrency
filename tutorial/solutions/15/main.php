<?php
/**
 * Use an event-loop instead of a try/catch statement to handle the close event
 */

use \parallel\Channel;
use \parallel\Events;
use \parallel\Events\Event;

$channel = Channel::make('channel');

$task = static function(Channel $channel) {
    $events = new Events();
    $events->addChannel($channel);
    foreach($events as $event) {
        switch ($event->type) {
            case Event\type::Read:
                $sleep_time = $event->value;
                sleep($sleep_time);
                echo ".";
                $events->addChannel($channel);
                break;
            case Event\type::Close:
                echo "bye\n";
                die;
        }   
    }
};

\parallel\run($task, [$channel]);

for ($i=1; $i < 5; $i++) {
    $channel->send(random_int(1,2));
}

$channel->close();
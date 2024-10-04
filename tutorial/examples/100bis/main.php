<?php
/**
 * Data-flow : create an adder waiting from two parallel channels to make an addition
 * using the event-loop to monitor channels
 */

use \parallel\{Channel,Events};

$channel1 = Channel::make('channel1', Channel::Infinite);
$channel2 = Channel::make('channel2', Channel::Infinite);

$source1 = \parallel\run(function($channel) {
    for ($i=1; $i <=10; $i++) {
        sleep(1);
        $channel->send(random_int(0,1000));
    }
    $channel->send(false);
}, [$channel1]);

$source2 = \parallel\run(function($channel) {
    for ($i=1; $i <=10; $i++) {
        sleep(2);
        $channel->send(random_int(0,1000));
    }
    $channel->send(false);
}, [$channel2]);

$events = new Events;
$events->addChannel($channel1);
$events->addChannel($channel2);
$value1 = null;
$value2 = null;

while ($event = $events->poll()) {
    if ($event->source == 'channel1') {
        $value1 = $event->value;
        if ($value1 === false) break;
    }
    if ($event->source == 'channel2') {
        $value2 = $event->value;
        if ($value2 === false) break;
    }

    // Wait for both values to make an addition
    if (is_null($value1) or is_null($value2)) continue;
    
    echo ($value1 + $value2) . PHP_EOL;
    $events->addChannel($channel1);
    $events->addChannel($channel2);
    $value1 = null;
    $value2 = null;
}

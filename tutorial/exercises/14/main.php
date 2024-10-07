<?php
/**
 * Modify this adder made up of two channels
 * to use an event-loop instead of an infinite loop 
 * to monitor channels in the main thread
 */

use \parallel\Channel;

 $channel1 = Channel::make('channel1', Channel::Infinite);
 $channel2 = Channel::make('channel2', Channel::Infinite);

 $source1 = \parallel\run(function($channel) {
    for ($i=1; $i<=4; $i++) {
        sleep(1);
        $channel->send(random_int(0,1000));
    }
    $channel->send(false);
 }, [$channel1]);

 $source2 = \parallel\run(function($channel) {
    for ($i=1; $i<=6; $i++) {
        sleep(2);
        $channel->send(random_int(0,1000));
    }
    $channel->send(false);
 }, [$channel2]);

while(true) {
    $value1 = $channel1->recv();
    if ($value1 === false) break;
    $value2 = $channel2->recv();
    if ($value2 === false) break;
    echo ($value1 + $value2) . PHP_EOL;
}

$source1->cancel();
$source2->cancel();


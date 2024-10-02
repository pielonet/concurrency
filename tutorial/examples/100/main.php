<?php
/**
 * Data-flow : create an adder waiting from two parallel channels to make an addition
 */

use \parallel\Channel;

 $channel1 = Channel::make('channel1', Channel::Infinite);
 $channel2 = Channel::make('channel2', Channel::Infinite);

 $source1 = \parallel\run(function($channel) {
    for ($i=1; $i <=10; $i++) {
        sleep(2);
        $channel->send(random_int(0,1000));
    }
    $channel->close();

 }, [$channel1]);

 $source2 = \parallel\run(function($channel) {
    for ($i=1; $i <=10; $i++) {
        sleep(2);
        $channel->send(random_int(0,1000));
    }
    $channel->close();
 }, [$channel2]);

while(true) {
    try {
        $value1 = $channel1->recv();
        $value2 = $channel2->recv();
        echo ($value1 + $value2) . PHP_EOL;
    } catch (\parallel\Channel\Error\Closed $e) {
        echo $e->getMessage() . PHP_EOL;
        break;
    }
}
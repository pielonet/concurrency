<?php
/* 
 * Sleep return value
 */

$sleep = function(string $who, int $min_sleep_time, int $max_sleep_time, array $statuses) {
    $sleep_time = rand($min_sleep_time, $max_sleep_time);
    sleep($sleep_time);
    $status_id = array_rand($statuses, 1);
    return [$who, $sleep_time, $statuses[$status_id]];
};


$ann_sleep = \parallel\run($sleep, ["Ann", 5, 10, ["well", "disturbed", "horribly"]]);


echo("zzz...\n");

list($who, $sleep_time, $status) = $ann_sleep->value();

echo("$who slept $status $sleep_time seconds");



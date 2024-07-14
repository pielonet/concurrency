<?php
/* 
 * Ann is having a snap
 * Future return value
 * 
 * public parallel\Future::value(): mixed
 * Shall return (and if necessary wait for) return from task 
 * @ref https://www.php.net/manual/en/parallel-future.value.php
 */

$sleep = function(string $who, int $min_sleep_time_seconds, int $max_sleep_time_seconds, array $statuses) {
    $sleep_time = rand($min_sleep_time_seconds, $max_sleep_time_seconds);
    sleep($sleep_time);
    $status_id = array_rand($statuses, 1);
    return [$who, $sleep_time, $statuses[$status_id]];
};


$ann_sleep = \parallel\run($sleep, ["Ann", 5, 10, ["well", "disturbed", "horribly"]]);


echo("zzz...". PHP_EOL);

list($who, $sleep_time, $status) = $ann_sleep->value();

echo("$who slept $status $sleep_time seconds". PHP_EOL);



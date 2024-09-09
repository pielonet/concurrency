<?php
/**
 *
 * Alice is having a snap
 * Future object and return value
 *
 * https://www.php.net/manual/en/class.parallel-future.php
 *
 * public parallel\Future::value(): mixed
 * Shall return (and if necessary wait for) return from task 
 * @ref https://www.php.net/manual/en/parallel-future.value.php
 */

use \parallel\Runtime;

$task = function (string $who, int $min_sleep_time_seconds, int $max_sleep_time_seconds, array $statuses) {
    $sleep_time = rand($min_sleep_time_seconds, $max_sleep_time_seconds);
    sleep($sleep_time);
    $status_id = array_rand($statuses, 1);
    return [$who, $sleep_time, $statuses[$status_id]];
};


$future = (new Runtime())->run($task, ["Alice", 5, 10, ["well", "disturbed", "horribly"]]);


echo "zzz...". PHP_EOL;

// Block execution until task finishes and get return value
[$who, $sleep_time, $status] = $future->value();

echo("$who slept $status $sleep_time seconds". PHP_EOL);



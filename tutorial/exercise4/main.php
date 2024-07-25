<?php
/* 
 * Alice and Bob sleep simultaneously
 * Complete the missing pieces of code so that you get their statuses as soon as they come back from sleep
 * 
 * public parallel\Future::done(): bool
 * Shall indicate if the task is completed 
 * @ref https://www.php.net/manual/en/parallel-future.done.php
 */

$sleep = function(string $who, int $min_sleep_time_seconds, int $max_sleep_time_seconds, array $statuses) {
    $sleep_time = rand($min_sleep_time_seconds, $max_sleep_time_seconds);
    echo("$who goes to sleep". PHP_EOL);
    sleep($sleep_time);
    $status_id = array_rand($statuses, 1);
    return [$who, $sleep_time, $statuses[$status_id]];
};


$futures[1] = \parallel\run($sleep, ["Alice", 6, 12, ["well", "disturbed", "horribly"]]);
$futures[2] = \parallel\run($sleep, ["Bob", 5, 10, ["quite well", "thoughtfully", "strangely"]]);

echo("zzz...\n");

while (/* REPLACE ME */) {
    foreach($futures as $key => $future) {
        if (/* REPLACE ME */) {
            [$who, $sleep_time, $status] = /* REPLACE ME */;
            echo("$who slept $status $sleep_time seconds". PHP_EOL);
            unset(/* REPLACE ME */);
        }
    }
}








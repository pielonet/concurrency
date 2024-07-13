<?php
/* 
 * 2 parallel sleepers
 */

$sleep = function(string $who, int $min_sleep_time_seconds, int $max_sleep_time_seconds, array $statuses) {
    $sleep_time = rand($min_sleep_time_seconds, $max_sleep_time_seconds);
    echo("$who goes to sleep". PHP_EOL);
    sleep($sleep_time);
    $status_id = array_rand($statuses, 1);
    return [$who, $sleep_time, $statuses[$status_id]];
};


$futures[1] = \parallel\run($sleep, ["Ann", 6, 12, ["well", "disturbed", "horribly"]]);
$futures[2] = \parallel\run($sleep, ["Bob", 5, 10, ["quite well", "thoughtfully", "strangely"]]);

echo("zzz...\n");

while (!empty($futures)) {
    foreach($futures as $key => $future) {
        if ($future->done()) {
            list($who, $sleep_time, $status) = $future->value();
            echo("$who slept $status $sleep_time seconds". PHP_EOL);
            unset($futures[$key]);
        }
    }
}








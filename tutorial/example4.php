<?php
/* 
 * Sleep again return value
 */

$sleep = function(int $min_sleep_time, int $max_sleep_time, array $statuses) {
    $sleep_time = rand($min_sleep_time, $max_sleep_time);
    sleep($sleep_time);
    $status_id = array_rand($statuses, 1);
    return [$sleep_time, $statuses[$status_id]];
};


$sleeps['Ann'] = \parallel\run($sleep, [5, 10, ["well", "disturbed", "horribly"]]);
$sleeps['Bob'] = \parallel\run($sleep, [6, 12, ["quite well", "thoughtfully", "strangely"]]);

echo("zzz...\n");

while (!empty($sleeps)) {
    foreach($sleeps as $who => $sleep) {
        if ($sleep->done()) {
            list($sleep_time, $status) = $sleep->value();
            echo("$who slept $status $sleep_time seconds\n");
            unset($sleeps[$who]);
        }
    }
}








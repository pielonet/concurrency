<?php
/* 
 * multiple sleepers in limited number of rooms
 */

include_once("config.php");

// Function that will be executed in each future (producer)
$sleep = function(string $who, int $min_sleep_time_seconds, int $max_sleep_time_seconds, array $statuses, string $room) {
    $sleep_time = rand($min_sleep_time_seconds, $max_sleep_time_seconds);
    echo("$who goes to sleep in $room". PHP_EOL);
    sleep($sleep_time);
    $status_id = array_rand($statuses, 1);
    return [$who, $sleep_time, $statuses[$status_id], $room];
};

// Reserve as many futures as there are rooms
// Initialize all futures to value "null" which means "unaffected = ready to run task"
$futures = array_fill(0, count($config['rooms']), null);

// Iterate over persons names with a generator
$names_generator = function(array $names) {
    // Shuffle names list for fun !
    shuffle($names);
    foreach ($names as $name)  {
        yield $name;
    }
};

$names = $names_generator($config['names']);

echo("zzz...". PHP_EOL);

while (!empty($futures)) {
    foreach($futures as $key => &$future) {
        if (is_null($future)) {
            // Future is unaffected
            if ($names->valid()) {
                // There are still persons available : put someone to sleep
                $name = $names->current();
                $future = \parallel\run(
                    $sleep,
                    [$name, $config['min_sleep_time_seconds'], $config['max_sleep_time_seconds'], $config['statuses'], $config['rooms'][$key]]
                );
                $names->next();
                continue;
            }

            // No more persons need to sleep, destroy future
            unset($futures[$key]);
            continue;
        }
        if ($future->done()) {
            list($who, $sleep_time, $status, $room) = $future->value();
            echo("$who slept $status $sleep_time seconds in $room". PHP_EOL);

            // Set future ready for new task
            $future = null;
        }
    }
}








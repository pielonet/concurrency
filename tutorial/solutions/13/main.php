<?php
/**
 * Parallelize many long running tasks
 * 
 */

$threads_count = 10;
$tasks_count = 100;

 $task = function(string $message): int {
    sleep(2);
    echo $message;
    return random_int(0,9);
 };

 function messages($tasks_count) {
    $characters = "abcdefghijklmnopqrstuvwxyz";
    for ($i=1; $i <=$tasks_count; $i++) {
        yield $characters[random_int(0, strlen($characters)-1)];
    }
 }

$generator = messages($tasks_count);

$futures = array_fill(0, $threads_count, null);
$task_number = 0;

while (! empty($futures)) {
    foreach($futures as $key => &$future) {
        if (is_null($future)) {
            if (! $generator->valid()) {
                unset($futures[$key]);
                continue;
            }
            $future = \parallel\run($task, [$generator->current()]);
            $generator->next();
            continue;
        }
        if ($future->done()) {
            $values[] = $future->value();
            $future = null;
        }
    }
}

echo "\n";

foreach($values as $value) {
    echo $value;
}

<?php
/**
 * Parallelize many long running tasks
 * Fill up missing pieces of code
 */

$threads_count = 10;
$tasks_count = 100;

 $task = function() {
    sleep(2);
    echo ".";
 };

$futures = array_fill(0, $threads_count, null);
$task_number = 0;

 while (! empty($futures)) {
    foreach($futures as $key => /* REPLACE ME */) {
        if (is_null($future)) {
            if ($task_number == $tasks_count) {
                unset(/* REPLACE ME */);
                continue;
            }
            $future = \parallel\run($task);
            $task_number++;
            continue;
        }
        if ($future->done()) {
            $future = /* REPLACE ME */;
        }
    }
 }



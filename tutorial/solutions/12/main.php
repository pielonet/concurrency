<?php
/**
 * Parallelize many long running tasks
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
    foreach($futures as $key => &$future) {
        if (is_null($future)) {
            if ($task_number == $tasks_count) {
                unset($futures[$key]);
                continue;
            }
            $future = \parallel\run($task);
            $task_number++;
            continue;
        }
        if ($future->done()) {
            $future = null;
        }
    }
 }

 

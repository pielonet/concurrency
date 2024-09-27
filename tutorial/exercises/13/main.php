<?php
/**
 * Parallelize many long running tasks
 * 
 * Modify following script so that
 * - The task receives a single string parameter 
 *   (a smallcap alphabetical character) and returns a single random decimal digit
 * - The list of parameters fed to the parallel tasks are produced by a generator
 * - The list of return values is displayed at the end
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



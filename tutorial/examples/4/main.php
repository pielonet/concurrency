<?php
/**
 * Do something while parallel task is running
 * 
 * public parallel\Future::done(): bool
 * Shall indicate if the task is completed 
 * @ref https://www.php.net/manual/en/parallel-future.done.php
 */


$task = function() {
    sleep(6);
    echo "done";
    return;
};

$future = \parallel\run($task);

while (! $future->done()) {
    echo ".";
    sleep(1);
}

// Result : ......done
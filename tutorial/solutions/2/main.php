<?php
/**
 * Use the given function as a task and launch at least 3 parallel tasks with it
 * 
 * Hint :
 * \parallel\run(Closure $task, array $argv): ?Future
 * Shall schedule task for execution in parallel, passing argv at execution time. 
 * @ref https://www.php.net/manual/en/parallel.run.php
 */

$task = function (string $message, int $sleep_time) {
    sleep($sleep_time);
    echo "$message\n";
};

$data = [["Hello", 1], ["Halo", 3], ["Hej", 2]];

foreach ($data as $datum) {
    \parallel\run($task, $datum);
}
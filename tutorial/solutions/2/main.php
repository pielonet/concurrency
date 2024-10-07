<?php
/**
 * Use the given function as a task and launch at least 3 parallel tasks with it
 * which will be run one after the other in the same parallel thread.
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

$runtime = new \parallel\Runtime();

foreach ($data as $datum) {
    $runtime->run($task, $datum);
}
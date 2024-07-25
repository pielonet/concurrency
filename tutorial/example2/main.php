<?php
/**
 * Molière !
 * Run many tasks in paralell with arguments
 *
 * parallel\run(Closure $task, array $argv): ?Future
 * Shall schedule task for execution in parallel, passing argv at execution time. 
 * @ref https://www.php.net/manual/en/parallel.run.php
 *
 * The use of this function is very similar to that of call_user_func_array:
 *  call_user_func_array(callable $callback, array $args): mixed
 *
 * with the following limitations
 * - variables can not be passed on by reference
 * - objects can not be passed on
 * - You can not use named variables in $argv
 * - $task must be a closure
 *
 * And other limitations (read the docs).
 */

$sentence = "Belle marquise, vos beaux yeux me font mourir d'amour";
$words = explode(" ", $sentence);

foreach($words as $word) {
    \parallel\run(
        function(string $word) {
            usleep(rand(1, 10000000));
            echo "$word ";
        },
        [$word]
    );
}

echo("$sentence". PHP_EOL);


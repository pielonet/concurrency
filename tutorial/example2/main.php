<?php
/**
 * Molière !
 * Run many tasks in paralell with arguments
 * 
 * parallel\run(Closure $task, array $argv): ?Future
 * Shall schedule task for execution in parallel, passing argv at execution time. 
 * @ref https://www.php.net/manual/en/parallel.run.php
 */

$sentence = "Belle marquise, vos beaux yeux me font mourir d'amour";
$words = explode(" ", $sentence);

foreach($words as $word) {
    \parallel\run(
        function($word) {
            usleep(rand(1, 10000000));
            echo "$word ";
        },
        [$word]
    );
}

echo("$sentence". PHP_EOL);


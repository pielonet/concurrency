<?php

/**
 * Exercise 1
 * Launch two parallel tasks.
 *  Second task must always complete
 *  before the first one.
 * 
 * Hint : 
 * \parallel\run(Closure $task): ?Future
 * Shall schedule task for execution in parallel.
 * @ref https://www.php.net/manual/en/parallel.run.php
 * 
 * Solution : see example/1/main.php
 */

\parallel\run(function() {
    sleep(3);
    echo "first\n";
});

\parallel\run(function() {
    sleep(1);
    echo "second\n";
});
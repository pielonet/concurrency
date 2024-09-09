<?php

/**
 * zzz !
 * Run two simple tasks in parallel
 *
 * parallel\run(Closure $task): ?Future
 * Shall schedule task for execution in parallel.
 * @ref https://www.php.net/manual/en/parallel.run.php
 */

$runtime1 = new \parallel\Runtime();
$runtime1->run(
    function() {
        sleep(5);
        echo " World !";
    }
);

$runtime2 = new \parallel\Runtime();
$runtime2->run(
    function() {
        sleep(2);
        echo "Hello";
    }
);

echo "zzz... ";

// Result : "zzz... Hello World !"
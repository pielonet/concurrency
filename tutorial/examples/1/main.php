<?php

/**
 * zzz !
 * Run two simple tasks in two parallel threads
 *
 * \parallel\run(Closure $task): ?Future
 * Shall schedule task for execution in parallel.
 * @ref https://www.php.net/manual/en/parallel.run.php
 * 
 */

\parallel\run(
    function() {
        sleep(5);
        echo " World !";
    }
);

\parallel\run(
    function() {
        sleep(2);
        echo "Hello";
    }
);

echo "zzz... ";

// Result : "zzz... Hello World !"
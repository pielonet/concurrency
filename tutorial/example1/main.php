<?php

/**
 * Bzz !
 * Run two simple tasks in parallel
 *
 * parallel\run(Closure $task): ?Future
 * Shall schedule task for execution in parallel.
 * @ref https://www.php.net/manual/en/parallel.run.php
 */

\parallel\run(
    function() {
        sleep(5);
        echo " la mouche";
    }
);

\parallel\run(
    function() {
        sleep(2);
        echo "Zobi";
    }
);

echo "Bzz ! ";

// Result : "Bzz ! Zobi la mouche"
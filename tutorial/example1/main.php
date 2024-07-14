<?php

/**
 * Run two tasks in parallel
 * 
 * parallel\run(Closure $task): ?Future
 * Shall schedule task for execution in parallel.
 * @ref https://www.php.net/manual/en/parallel.run.php
 */

\parallel\run(
    function() {
        sleep(5);
        echo(" la mouche");
    }
);

\parallel\run(
    function() {
        sleep(2);
        echo("zobi");
    }
);

// Result : "zobi la mouche"
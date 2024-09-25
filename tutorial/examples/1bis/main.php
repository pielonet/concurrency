<?php

/**
 * zzz !
 * Run two simple tasks in the same thread sequentially
 *
 */
// final class parallel\Runtime {
//     /* Create */
//     public __construct()
//     public __construct(string $bootstrap)
//     /* Execute */
//     public run(Closure $task): ?Future
//     public run(Closure $task, array $argv): ?Future
//     /* Join */
//     public close(): void
//     public kill(): void
// }
 /**
 * The \parallel\Runtime class
 * @ref https://www.php.net/manual/en/class.parallel-runtime.php
 */

$runtime = new \parallel\Runtime();
$runtime->run(
    function() {
        sleep(1);
        echo "Hello ";
    }
);

$runtime->run(
    function() {
        sleep(1);
        echo "World !";
    }
);

echo "zzz... ";

$runtime->close();

// Result : "zzz... Hello World !"
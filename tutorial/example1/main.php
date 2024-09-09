<?php

/**
 * zzz !
 * Run two simple tasks in two parallel threads
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

$runtime1->close();
$runtime2->close();

// Result : "zzz... Hello World !"
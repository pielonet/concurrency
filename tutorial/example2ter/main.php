<?php

/**
 * Playing with runtimes
 * Tasks scheduled in the same runtime share the same global scope
 */

$fun = function() {
     if (!array_key_exists('counter', $GLOBALS)) {
         $GLOBALS['counter'] = 0;
     }
     $GLOBALS['counter']++;
     echo "counter is at " . $GLOBALS['counter'] . PHP_EOL;
 };
 
 $runtime = new \parallel\Runtime();
 
 for ($i = 0; $i < 100; $i++) {
     $runtime->run($fun);
 }

// Results in :
/*
counter is at 1
counter is at 2
counter is at 3
counter is at 4
counter is at 5
counter is at 6
counter is at 7
counter is at 8
counter is at 9
counter is at 10
counter is at 11
counter is at 12
counter is at 13
counter is at 14
counter is at 15
counter is at 16
...
*/
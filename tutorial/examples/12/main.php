<?php

/**
 * Open a new thread from a secondary thread
 */

 (new \parallel\Runtime())->run(function() {
    echo "Hello ";
    $future = (new \parallel\Runtime())->run(function() {
        sleep(2);
        return "New World" . PHP_EOL;
    });
    echo $future->value();
 });
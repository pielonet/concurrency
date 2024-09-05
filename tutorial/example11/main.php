<?php

/**
 * Run thread in thread
 */

 \parallel\run(function() {
    echo "Hello ";
    $future = \parallel\run(function() {
        sleep(2);
        return "New World" . PHP_EOL;
    });
    echo $future->value();
 });
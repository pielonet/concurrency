<?php

/**
 * Run thread in thread
 */

 \parallel\run(function() {
    \parallel\run(function() {
        echo "Hello New World" . PHP_EOL;
    });
 });
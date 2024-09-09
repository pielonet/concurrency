<?php

/**
 * Playing with the functional API
 */

$fun = function() {
    if (!array_key_exists('counter', $GLOBALS)) {
        $GLOBALS['counter'] = 0;
    }
    $GLOBALS['counter']++;
    echo "counter is at " . $GLOBALS['counter'] . PHP_EOL;
};

for ($i = 0; $i < 100; $i++) {
    \parallel\run($fun);
}
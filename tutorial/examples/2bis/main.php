<?php

/**
 * Playing with the functional API
 * Warning with cached runtimes : they share the same global scope !!
 */

$fun = function() {
    if (!array_key_exists('counter', $GLOBALS)) {
        $GLOBALS['counter'] = 0;
    }
    $GLOBALS['counter']++;
    echo "counter is at " . $GLOBALS['counter'] . PHP_EOL;
};

for ($i = 0; $i < 20; $i++) {
    \parallel\run($fun);
}

// Results are unpredictable !
<?php

/**
 * Exercise 1
 * Fetch a URL in the main thread
 * Launch a parallel task to fetch the second URL in parallel with the main thread
 * Compare execution time with the "classic" sequential processing
 * 
 * Hint : 
 * \parallel\run(Closure $task): ?Future
 * Shall schedule task for execution in parallel.
 * @ref https://www.php.net/manual/en/parallel.run.php
 * 
 */

$urls = ["https://afup.org", "https://www.php.net"];

echo "Sequential processing -> ";

$start_time = microtime(true);

$html = file_get_contents($urls[0]);
$html = file_get_contents($urls[1]);

echo "Duration: " . microtime(true) - $start_time . PHP_EOL;

// ------------------------

echo "Parallel processing -> ";

$start_time = microtime(true);

/* == YOUR CODE HERE == */

echo "Duration: " . microtime(true) - $start_time . PHP_EOL;
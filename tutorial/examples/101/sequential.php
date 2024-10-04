<?php
/**
 * Compute the nth first primes sequentially (much faster than in parallel) 
 */

$primes_requested = 100_000;
$echo = false;

// ==========================

function withList($primes_requested, $echo) {
    $primes = [];
    $primes[] = 2;
    $primes_count = 1;
    $time_start = microtime(true);
    for ($i=3 ; ; $i++) {
        $sqrt_computed = false;
        foreach($primes as $prime) {
            if ($i%$prime == 0) break;
            if (! $sqrt_computed) {
                $square_root = sqrt($i);
                $sqrt_computed = true;
            }
            if ($prime > $square_root) {
                $primes[] = $i;
                $primes_count++;
                if ($echo) echo "$i\n";
                break;
            }
        }
        if ($primes_count == $primes_requested) break;
    }
    echo "Duration: " . microtime(true) - $time_start . PHP_EOL;
}

function withoutList($primes_requested, $echo) {
    $primes = [];
    if ($echo) echo "2\n";
    $primes[] = 2;
    $primes_count = 1;
    $time_start = microtime(true);
    for ($i=3 ; ; $i++) {
        $sqrt_computed = false;
        for ($j=2; ; $j++) {
            if ($i%$j == 0) break;
            if (! $sqrt_computed) {
                $square_root = sqrt($i);
                $sqrt_computed = true;
            }
            if ($j > $square_root) {
                if ($echo) echo "$i\n";
                $primes[] = $i;
                $primes_count++;
                break;
            }
        }
        if ($primes_count == $primes_requested) break;
    }
    echo "Duration: " . microtime(true) - $time_start . PHP_EOL;
}

withList($primes_requested, $echo);
sleep(1);
withoutList($primes_requested, $echo);
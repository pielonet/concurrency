 <?php

/**
 * Sample parallel Runtime/Future functional API processing
 * using a generator to produce the list of items to process
 * 
 * Items to process in parallel come from a generator
 * It could be anything : e.g fetch a mysql array, a DirectoryIterator...
 * Thus the number of items to process in parallel is NOT known in advance
 * 
 * This algorithm attributes items to each parallel thread dynamically
 * As soon as a thread has finished working
 * It is assigned a new item to process
 * until all items are processed (generator closes)
 * 
 * In this example we process 50 items in 5 parallel threads
 * It produces output in this form (output changes at each run) :
 * 
 * ThreadId: 1 => Item: 1 (Start)
 * ThreadId: 2 => Item: 2 (Start)
 * ThreadId: 3 => Item: 3 (Start)
 * ThreadId: 4 => Item: 4 (Start)
 * ThreadId: 5 => Item: 5 (Start)
 * ThreadId: 5 => Item: 5 Sleep: 3s (End)
 * ThreadId: 5 => Item: 6 (Start)
 * ThreadId: 3 => Item: 3 Sleep: 4s (End)
 * ThreadId: 3 => Item: 7 (Start)
 * ThreadId: 2 => Item: 2 Sleep: 6s (End)
 * ThreadId: 2 => Item: 8 (Start)
 * ...
 * ThreadId: 4 => Item: 44 Sleep: 6s (End)
 * ThreadId: 4 => Item: 49 (Start)
 * ThreadId: 3 => Item: 46 Sleep: 5s (End)
 * ThreadId: 3 => Item: 50 (Start)
 * ThreadId: 2 => Item: 43 Sleep: 9s (End)
 * Destroy ThreadId: 2
 * ThreadId: 1 => Item: 47 Sleep: 5s (End)
 * Destroy ThreadId: 1
 * ThreadId: 4 => Item: 49 Sleep: 7s (End)
 * Destroy ThreadId: 4
 * ThreadId: 5 => Item: 48 Sleep: 10s (End)
 * Destroy ThreadId: 5
 * ThreadId: 3 => Item: 50 Sleep: 10s (End)
 * Destroy ThreadId: 3
 */

include_once("lib/Pool.php");

use \Concurrency\Pool;

// Function executing in each thread. Have a snap for a random time for example !
// You could make some calculation here or transfer files or whatever...
$task = function (int $item_id, int $sleep_seconds) {
    echo "Item $item_id sleeps for {$sleep_seconds}s\n";
    sleep($sleep_seconds);
    return [$item_id, $sleep_seconds];
};


// Generate list of items to process with a generator
function generator(int $item_count) {
    echo "Item count: $item_count\n";
    for ($item_id=1; $item_id <= $item_count; $item_id++) {
        $sleep_seconds = rand(1, 10);
        yield [$item_id, $sleep_seconds];
    }
}

// Set item_count to a random number so that each run is different
$item_count= rand(1, 20);
// Create generator
$generator = generator($item_count);

$fulfilled = function (int $item_id, int $sleep_seconds) {
    echo "Item $item_id slept for {$sleep_seconds}s\n";
    return compact('item_id', 'sleep_seconds');
};

$pool = new Pool(
    concurrency: 5,
    task: $task,
    generator: $generator,
    fulfilled: $fulfilled
);

$pool->wait();


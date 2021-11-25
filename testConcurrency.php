<?php

/**
 * Sample parallel Runtime/Future processing
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


// Generate list of items to process with a generator
function generator(int $item_count) {
    echo "Item count: $item_count\n";
    for ($i=1; $i <= $item_count; $i++) {
        $sleep_seconds = rand(1, 10);
        yield [$i, $sleep_seconds];
    }
}

function testConcurrency(int $concurrency, int $max_item_count) {

    // Set item_count to a random number so that each run is different
    $item_count= rand(1, $max_item_count);
    // Create generator
    $generator = generator($item_count);

    // Function executing in each thread. Have a snap for a random time for example !
    // You could make some calculation here or transfer files or whatever...
    $producer = function (int $item_id, int $sleep_seconds) {
        sleep($sleep_seconds);
        return ['item_id' => $item_id, 'sleep_seconds' => $sleep_seconds];
    };

    // Fill up threads with a single future initialized to 'false' value
    for ($thread_id = 1; $thread_id <= $concurrency; $thread_id++) {
        $threads[$thread_id] = ['runtime' => new \parallel\Runtime()];
    }
    
    // Create infinite loop for the whole processing
    while (true) {
        // Loop through threads until generator closes and all threads are destroyed
        foreach ($threads as $thread_id => &$thread) {
            if (!isset($thread['future']) and $generator->valid()) {
                // Thread is inactive and generator still has values : run something in the thread
                list($item_id, $sleep_seconds) = $generator->current();
                $thread['future'] = $thread['runtime']->run($producer, [$item_id, $sleep_seconds]);
                echo "ThreadId: $thread_id => Item: $item_id Sleep: {$sleep_seconds}s (Start)\n";
                $generator->next();
            } elseif (!isset($thread['future'])) {
                // Destroy thread in case generator is closed
                echo "Destroy ThreadId: $thread_id\n";
                unset($threads[$thread_id]);
            } elseif ($thread['future']->done()) {
                // Thread finished task. Get result value.
                $item = $thread['future']->value();
                echo "ThreadId: $thread_id => Item: {$item['item_id']} (End)\n";
                // Set thread ready to run again
                unset($thread['future']);
            }
        }

        // Escape infinite loop when all threads are destroyed
        if (empty($threads)) break;
    }
}

$concurrency = 5;
$item_count = 50;

testConcurrency($concurrency, $item_count);

?>
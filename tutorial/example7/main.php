<?php
/**
 * Parallel SSH with SSH2 extension
 */


include_once('config.php');

// Function that will be executed in each future (task)
$task = function (array $config, int $task_id, string $command) {
    include_once('SSH2.php');
    $ssh_connection = new SSH2($config);
    $response = $ssh_connection->exec($command);
    return [$task_id, $response];
};

// Reserve as many futures as there are rooms
// Initialize all futures to value "null" which means "unaffected = ready to run task"
$futures = array_fill(0, $config['concurrency'], null);


// Iterate over persons names with a generator
function generator(int $commands_count, string $command) {
    for ($i=1; $i <= $commands_count; $i++) {
        yield [$i, $command];
    }
};

$commands = generator($config['commands_count'], $config['command']);

while (!empty($futures)) {
    foreach($futures as $key => &$future) {
        if (is_null($future)) {
            // Future is unaffected
            if ($commands->valid()) {
                // There are still commands to run
                list($task_id, $command) = $commands->current();
                $future = \parallel\run(
                    $task,
                    [$config, $task_id, $command]
                );
                $commands->next();
                continue;
            }

            // No more persons need to sleep, destroy future
            unset($futures[$key]);
            continue;
        }
        if ($future->done()) {
            list($task_id, $response) = $future->value();
            echo "Task: $task_id, Response: $response";

            // Set future ready for new task
            $future = null;
        }
    }
}
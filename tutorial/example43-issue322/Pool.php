<?php

namespace Concurrency;

class Pool {
    /**
     * Max number of concurrent threads
     */
    private int $concurrency;

    /**
     * A closure task to be executed in parallel threads
     * The list of its parameters must match the values of the array
     * returned by the generator
     */
    private \Closure $task;

    /**
     * A generator that produces the array of parameters
     * fed into the task. The values of that array must 
     * match the task's list of parameters.
     */
    private \Generator $generator;

    /**
     * A file that will be added to each Runtime
     * to bootstrap code
     */
    private string $bootstrap;

    /**
     * An array containing all the returned values
     * from the fulfilled tasks.
     */
    private array $values;

    public function __construct(\Closure $task, \Generator $generator, int $concurrency = 5, string $bootstrap = "") {
        //Populate private variables
        $this->concurrency = $concurrency;
        $this->task = $task;
        $this->generator = $generator;
        if ($bootstrap != "") {
            \parallel\bootstrap($bootstrap);
        }
    }

    public function values() : array {

        //Return error if wait was already called
        if (isset($this->values)) {
            throw new \Exception("wait() method can only be called once");
        }

        // Reserve as many futures as there are parallel threads required
        // Initialize all futures to value "null" which means "unaffected = ready to run task"
        $futures = array_fill(0, $this->concurrency, null);

        while (!empty($futures)) {
            foreach($futures as $key => &$future) {
                if (is_null($future)) {
                    // Future is unaffected
                    if ($this->generator->valid()) {
                        // There are still tasks to run
                        $task_parameter = $this->generator->current();
                        // Make simple coherence checks
                        if (! is_array($task_parameter)) {
                            throw new \Exception("Invalid generator return value. Generator return value must be of type array");
                        }
                        $future = \parallel\run($this->task, $task_parameter);
                        $this->generator->next();
                        continue;
                    }

                    // No more tasks to perform, destroy future
                    unset($futures[$key]);
                    continue;
                }
                if ($future->done()) {
                    $values[] = $future->value();

                    // Set future ready for new task
                    $future = null;
                }
            }
            // Destroy the last reference
            unset($future);
        }

        $this->values = $values;
        return $values;
    }

}
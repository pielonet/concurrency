<?php

namespace Concurrency;

use \parallel\Runtime;

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

    public function __construct(\Closure $task, \Generator $generator, int $concurrency = 5 , string $bootstrap = "") {
        //Populate private variables
        $this->concurrency = $concurrency;
        $this->task = $task;
        $this->generator = $generator;
        $this->bootstrap = $bootstrap;
    }

    /**
     * Launch pool and return an array containing
     * all returned values from each single task
     * 
     * @return array $values
     */
    public function values() : array {

        // Directly return values if method was already called
        if (isset($this->values)) {
            return $this->values;
        }

        // Reserve as many futures as there are parallel threads required
        // Initialize all futures to value null which means "unaffected = ready to run task"
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
                        if ($this->bootstrap != "") {
                            $runtimes[$key] = new Runtime($this->bootstrap);
                        } else {
                            $runtimes[$key] = new Runtime();
                        }
                        $future = $runtimes[$key]->run($this->task, $task_parameter);
                        $this->generator->next();
                        continue;
                    }

                    // No more tasks to perform, destroy future and close runtime
                    unset($futures[$key]);

                    continue;
                }

                if ($future->done()) {
                    // Future has run its task, retrieve return value
                    $values[] = $future->value();

                    // Set future ready for new task and close thread
                    $future = null;
                    $runtimes[$key]->close();
                }
            }
            // Destroy the last reference
            unset($future);
        }

        $this->values = $values;
        return $values;
    }
}
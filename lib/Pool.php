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
     * A closure to be executed once a task is fulfilled
     * The list of its parameters must match the values of the array
     * returned by the task
     */
    private \Closure $fulfilled;

    /**
     * An array containing all the returned values
     * from the fulfilled function.
     */
    private array $response;

    public function __construct(\Closure $task, \Generator $generator, \Closure $fulfilled, int $concurrency = 5 ) {
        $this->concurrency = $concurrency;
        $this->task = $task;
        $this->generator = $generator;
        $this->fulfilled = $fulfilled;
    }

    public function wait() {
        // Reserve as many futures as there are rooms
        // Initialize all futures to value "null" which means "unaffected = ready to run task"
        $futures = array_fill(0, $this->concurrency, null);

        $task_parameters = [];

        while (!empty($futures)) {
            foreach($futures as $key => &$future) {
                if (is_null($future)) {
                    // Future is unaffected
                    if ($this->generator->valid()) {
                        // There are still tasks to run
                        $task_parameters = $this->generator->current();
                        $future = \parallel\run(
                            $this->task,
                            $task_parameters
                        );
                        $this->generator->next();
                        continue;
                    }

                    // No more tasks to perform, destroy future
                    unset($futures[$key]);
                    continue;
                }
                if ($future->done()) {
                    $response = $future->value();
                    $grand_response[] = call_user_func_array($this->fulfilled, $response);

                    // Set future ready for new task
                    $future = null;
                }
            }
        }

        $this->response = $grand_response;
    }

    public function getResponse() {
        if (!isset($this->response)) {
            throw new \Exception("Call function wait before");
        }

        return $this->response;
    }
}
<?php

namespace Concurrency;

class Pool {
    private int $concurrency;

    private \Closure $task;

    private \Generator $generator;

    private \Closure $fulfilled;

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
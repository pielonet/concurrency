# concurrency
PHP parallel programming tutorial and examples using php/parallel extension

- PHP/parallel optimized run examples (runs mostly the same as Guzzle/HTTP promises https://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests)
- Comparison between single and multiple waiting queues (at post office...)

## How to run

- Install Docker

- Run a script e.g simulateQueue.php (may take much time on the first run since the Docker container is being build)
```bash
./run.sh simulateQueue/main.php
```

### Scripts

- `simulateQueue` : compare single and multiple queues average and max waiting time

### Tutorial

Example are ordered by difficulty, starting with very easy examples.

Each exampleX (where X stands for the number of the example) can be run with the following command :
```bash
./run.sh tutorial/exampleX/main.php
```


- `example1` : "zzz !", Run two simple tasks in parallel
- `example2` : "Molière !" Run many tasks in parallel with arguments
- `example3` : "Alice is having a snap !", Future object and return value
- `example4` : "Alice and Bob sleep simultaneously", Get Future return value as soon as it completes
- `example5` : "multiple sleepers in limited number of rooms", Launch tasks in parallel in a limited number of threads and wait for them to complete
- `example6` : "http only !", Parallel download with Guzzle 7
- `example7` : "SSH the PHP way ! ", Parallel SSH with SSH2 extension
- `example8` : "generic !", Parallel SSH as exec shell command and generic concurrency class
- `example9` : "Pong...zzz!", Run two simple tasks in parallel and synchronize them with a channel
- `example10` : Single queue parallel simulation
- `example11` : Multiple queues parallel simulation
- `example12` : Thread in thread
- `example42` : Benchmark single and multiple queues in parallel
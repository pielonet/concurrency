# concurrency
Parallel processing and waiting queue simulations

- PHP/parallel optimized run examples (runs mostly the same as Guzzle/HTTP promises https://docs.guzzlephp.org/en/stable/quickstart.html#concurrent-requests)
- Comparison between single and multiple waiting queues (at post office...)

## How to run

- Install Docker
- Build concurrency php container
```
docker build --tag php:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) .
```
- Run a script e.g simulateQueue.php
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


- `example1` : "Bzz !", Run two simple tasks in parallel
- `example2` : "Molière !" Run many tasks in parallel with arguments
- `example3` : "Alice is having a snap !", Future object and return value
- `example4` : "Alice and Bob sleep simultaneously", Get Future return value as soon as it completes
- `example5` : "multiple sleepers in limited number of rooms", Launch tasks in parallel in a limited number of threads and wait for them to complete
- `example6` : "http only !", Parallel download with Guzzle 7
- `example7` : "SSH the PHP way ! ", Parallel SSH with SSH2 extension
- `example8` : "generic !", Parallel SSH as exec shell command and generic concurrency class
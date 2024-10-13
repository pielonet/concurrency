# concurrency
PHP parallel programming tutorial and examples using [php/parallel](https://www.php.net/manual/en/book.parallel.php) extension

- Comparison between single and multiple waiting queues (at post office...)
- PHP/parallel optimized run examples
- Step by step tutorial with exercises and solutions

## How to run examples

- [Install Docker](https://docs.docker.com/engine/install/)

- Run a script e.g simulateQueue.php (may take much time on the first run since the Docker container is being build)
```bash
./run.sh -b simulateQueue/main.php
```

Command options :
- -b 
    Builds container. This option MUST be used on first run ! It is optional afterwards.
- -i [official|frankenphp]
     Whether to use the offical PHP Docker image or that of FrankenPHP project. Defaults to "official"
- -d `<Docker-options>`
    Set specific Docker/run options. Useful to control CPU or memory use by Docker. Defaults to "--cpus=2.0".

### Scripts

- `simulateQueue` : compare single and multiple queues average and max waiting time. The queues are implemented as sequential programs. Multiple simulations ar run in parallel.
- `lib/Pool.php` : a simple "Pool" class to run any number of tasks in parallel with a defined concurrency. Can be reused in any project !

### Tutorial

Example are ordered by difficulty, starting with very easy examples.

Each examples/X (where X stands for the number of the example) can be run with the following command :
```bash
./run.sh -b tutorial/example/X/main.php
```


- `1` : "zzz !", Run two simple tasks in parallel
- `2` : "Molière !" Run many tasks in parallel with arguments
- `3` : "Alice is having a snap !", Future object and return value
- `4` : "Alice and Bob sleep simultaneously", Get Future return value as soon as it completes
- `5` : "multiple sleepers in limited number of rooms", Launch tasks in parallel in a limited number of threads and wait for them to complete
- `6` : "http only !", Parallel download with Guzzle 7
- `7` : "SSH the PHP way ! ", Parallel SSH with SSH2 extension
- `8` : "generic !", Parallel SSH as exec shell command and generic concurrency class
- `9` : "Pong...zzz!", Run two simple tasks in parallel and synchronize them with a channel
- `10` : Single queue parallel simulation
- `11` : Multiple queues parallel simulation
- `12` : Parallel thread launched from a parallel thread
- `42` : Benchmark single and multiple queues in parallel
- `100` : Data-flow : create an adder waiting from two parallel channels to make an addition
- `101` : Prime sieve with daisy-chain filter processes compared to sequential algorithm
- `102` : Fibonacci : Mimics the Go "select" statement with event-loop

## Utils (Linux)

- Get number of threads for your process
```bash
ps -o thcount <pid>
```

- Monitor context switches per second : watch "cs" value
```bash
vmstat 1
```

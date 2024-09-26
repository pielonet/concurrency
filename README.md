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

Command options :
- -b                        Builds container. This option MUST be used on first run ! It is optional afterwards.
- -r [official|frankenphp]  Whether to use the offical PHP Docker image or that of FrankenPHP project. Defaults to "official"
- -d "<Docker-options>"     Set specific Docker/run options. Useful to control CPU or memory use by Docker. Defaults to "--cpus=2.0".

### Scripts

- `simulateQueue` : compare single and multiple queues average and max waiting time. The queues are implemented as sequential programs. Multiple simulations ar run in parallel.
- `lib/Pool.php` : a simple "Pool" class to run any number of tasks in parallel with a defined concurrency. Can be reused in any project !

### Tutorial

Example are ordered by difficulty, starting with very easy examples.

Each exampleX (where X stands for the number of the example) can be run with the following command :
```bash
./run.sh tutorial/example/X/main.php
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
- `12` : Thread in thread
- `42` : Benchmark single and multiple queues in parallel
# concurrency
Parallel processing and waiting queue simulations

## How to run

- Install Docker
- Build concurrency php container
```
docker build --tag php:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) .
```
- Run a script e.g simulateQueue.php
```
docker container run --rm --user $(id -u):$(id -g) -v $(pwd):/app/ php:concurrency php /app/simulateQueue.php
```
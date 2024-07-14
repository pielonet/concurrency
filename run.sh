#!/bin/bash

usage='
Usage
-----
./run.sh <relative_script_path>
'

script_path=$1

[[ -f "./$script_path" ]] || { echo "Error: file does not exist"; echo "$usage"; exit 1; }

docker build --quiet --tag php:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) .
time docker container run --rm -v $(pwd):/app/ php:concurrency php /app/$script_path | tee out.log

# Prerequisite for tutorial example6
# cd tutorial/example6
# docker container run --rm  -v $(pwd):/app/ --workdir /app php:concurrency composer require guzzlehttp/guzzle:^7.0
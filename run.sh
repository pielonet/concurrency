#!/bin/bash

usage='
Usage
-----
./run.sh <relative_script_path> [runtime] [docker_options]
'

script_path=$1

[[ -z $2 ]] && runtime=official || runtime=$2

[[ -z $3 ]] && docker_options='--cpus=2.0' || docker_options=$3

[[ -f "./$script_path" ]] || { echo "Error: file does not exist"; echo "$usage"; exit 1; }


if [[ "$runtime" = "official" ]]; then
    docker build --quiet --tag php:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) ./Dockerfiles/official
    time docker run "$docker_options" --rm -v $(pwd):/app/ php:concurrency php /app/$script_path | tee out.log
elif [[ "$runtime" = "frankenphp" ]]; then
    docker build --quiet --tag frankenphp:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) ./Dockerfiles/frankenphp
    time docker run "$docker_options" --rm -v $(pwd):/app/public frankenphp:concurrency frankenphp php-cli /app/public/$script_path  | tee out.log
    #docker container rm -f franken-concurrency
    #docker run  --name franken-concurrency -v $(pwd):/app/public -p 8080:80 -p 8443:443 -p 8443:443/udp frankenphp:concurrency
    #sleep 2
    #time curl -k https://localhost:8443/$script_path
fi


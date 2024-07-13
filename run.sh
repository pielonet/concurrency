#!/bin/bash

usage='
Usage
-----
./run.sh <relative_script_path>
'

script_path=$1

[[ -f "./$script_path" ]] || { echo "Error: file does not exist"; echo "$usage"; exit 1; }

time docker container run --rm --user $(id -u):$(id -g) -v $(pwd):/app/ php:concurrency php /app/$script_path | tee out.log
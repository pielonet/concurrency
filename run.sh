#!/bin/bash

function usage {
       printf "Usage: ./run.sh <options> <relative_script_path>\n"
       printf "Options:\n"
       printf " -h                               Display this help message.\n"
       printf " -b                               Builds parallel container. MUST be used on first run.\n"
       printf " -d <docker_options>              Set specific Docker/run options. Useful to control CPU or memory use by Docker. Defaults to '--cpus=2.0'.\n"
       printf " -i [official|frankenphp]         Specify which Docker image to use. Defaults to official.\n"
       exit 1
}

while getopts "hbd:i:" opt; do
    case "${opt}" in
        h)
            usage
            ;;

        b)
            build=true
            ;;

        d)
            docker_options=${OPTARG}
            ;;

        i)
            i=${OPTARG}
            [[ $i = "official" || $i = "frankenphp" ]] || { echo "Invalid runtime argument $i"; usage; }
            image=$i
            ;;

        *)
            printf "Invalid Option: $1.\n"
            usage
            ;;
    esac
done

shift $((OPTIND-1))

script_path=$1

[[ -z $image ]] && image=official

[[ -z $docker_options ]] && docker_options='--cpus=2.0'

[[ -f "./$script_path" ]] || { echo "Error: file does not exist"; usage; }

[[ -z "$2" ]] || { echo "Too many arguments"; usage; }


if [[ "$image" = "official" ]]; then
    [[ "$build" = "true" ]] && docker build --tag php:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) ./Dockerfiles/official
    docker run "$docker_options" --rm -v ./php-ini-overrides.ini:/usr/local/etc/php/conf.d/php-ini-overrides.ini -v $(pwd):/app/ php:concurrency php /app/$script_path | tee out.log
elif [[ "$image" = "frankenphp" ]]; then
    [[ "$build" = "true" ]] && docker build --tag frankenphp:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) ./Dockerfiles/frankenphp
    docker run "$docker_options" --rm -v ./php-ini-overrides.ini:/usr/local/etc/php/conf.d/php-ini-overrides.ini -v $(pwd):/app/public frankenphp:concurrency frankenphp php-cli /app/public/$script_path  | tee out.log
    #docker container rm -f franken-concurrency
    #docker run  --name franken-concurrency "$docker_options" -v ./php-ini-overrides.ini:/usr/local/etc/php/conf.d/php-ini-overrides.ini -v $(pwd):/app/public -p 8080:80 -p 8443:443 -p 8443:443/udp frankenphp:concurrency
    #sleep 2
    #curl -k https://localhost:8443/$script_path
fi

echo


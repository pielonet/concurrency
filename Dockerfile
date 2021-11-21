#docker build --tag php:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) .
FROM php:7.2-zts

RUN apt-get update \
  && apt-get -y install openssh-client git

RUN pecl install parallel-1.1.4 \
  && docker-php-ext-enable parallel

RUN pecl install stats-2.0.3 \
  && docker-php-ext-enable stats

# accept the arguments from build-args
ARG PUID 
ARG PGID
ARG USER

# Add the group (if not existing) 
# then add the user to the numbered group 
RUN groupadd -g ${PGID} ${USER} || true && \
    useradd --create-home --uid ${PUID} --gid `getent group ${PGID} | cut -d: -f1` ${USER} || true 

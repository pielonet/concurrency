#docker build --tag php:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) .
FROM php:8.3-zts-alpine

#RUN apk add --no-cache openssh

# Install the necessary packages to install pecl extensions
# @ref https://stackoverflow.com/questions/61282013/pecl-package-installation-fail-in-docker
RUN apk add --no-cache --virtual .phpize-deps-configure $PHPIZE_DEPS

# Install PHP/parallel extension
# @ref https://pecl.php.net/package/parallel
# @ref https://github.com/krakjoe/parallel
RUN pecl install parallel-1.2.2 \
  && docker-php-ext-enable parallel

# accept the arguments from build-args
ARG PUID 
ARG PGID
ARG USER

# Add the group (if not existing) 
# then add the user to the numbered group 
# @ref https://www.baeldung.com/ops/docker-set-user-container-host
RUN addgroup -g ${PGID} -S ${USER} || true && \
    adduser -S -G ${USER} -h /home/${USER} -u ${PUID} ${USER} || true

USER ${USER}

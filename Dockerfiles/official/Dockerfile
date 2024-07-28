#docker build --tag php:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) ./Dockerfiles/official/
FROM php:8.3-zts-alpine

# Install php extension installation script
# @ref https://github.com/mlocati/docker-php-extension-installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions parallel-1.2.3 ssh2-1.4.1 @composer

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

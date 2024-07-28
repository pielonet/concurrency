#docker build --tag frankenphp:concurrency --build-arg PUID=$(id -u) --build-arg PGID=$(id -g) --build-arg USER=$(id -un) ./Dockerfiles/frankenphp
FROM dunglas/frankenphp

# Install extensions
RUN install-php-extensions parallel-1.2.3 ssh2-1.4.1 @composer

# accept the arguments from build-args
ARG PUID 
ARG PGID
ARG USER

# Add the group (if not existing) 
# then add the user to the numbered group 
RUN \
    groupadd -g ${PGID} ${USER} || true && \
	# Use "adduser -D ${USER}" for alpine based distros
	useradd --create-home --uid ${PUID} --gid `getent group ${PGID} | cut -d: -f1` ${USER} || true && \
	# Add additional capability to bind to port 80 and 443
	setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
	# Give write access to /data/caddy and /config/caddy
	chown -R ${USER}:${USER} /data/caddy && chown -R ${USER}:${USER} /config/caddy

USER ${USER}
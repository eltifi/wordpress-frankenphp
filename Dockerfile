ARG WORDPRESS_VERSION=latest
ARG PHP_VERSION=8.5
ARG USER=www-data

FROM wordpress:$WORDPRESS_VERSION AS wp
FROM dunglas/frankenphp:php${PHP_VERSION}-alpine AS base

ARG LANG en_US.utf8
ARG USER=www-data
ARG UID=1000
ARG GID=1000

LABEL org.opencontainers.image.title="WordPress with FrankenPHP"
LABEL org.opencontainers.image.description="Optimized WordPress container with FrankenPHP & Caddy"

ENV TZ=America/New_York

# FrankenPHP and Caddy configuration
ENV FORCE_HTTPS=0
ENV FRANKENPHP_CONFIG=

# Caddy environment variables
ENV CADDY_SERVER_OPTIONS=""
ENV CADDY_SERVER_TLS=""
ENV CADDY_SERVER_ROOT="/var/www/html"
ENV SERVER_NAME="localhost"

# WordPress database configuration (defaults - override in docker-compose.yml)
ENV WORDPRESS_DB_HOST="mysql:3306"
ENV WORDPRESS_DB_NAME="wordpress"
ENV WORDPRESS_DB_USER="wordpress"
ENV WORDPRESS_DB_CHARSET="utf8mb4"
ENV WORDPRESS_DB_COLLATE="utf8mb4_unicode_ci"

# WordPress authentication keys and salts (passed via docker-compose .env - NOT set in Dockerfile)
# These are sensitive and should not be baked into the image

# WordPress additional configuration
ENV WORDPRESS_TABLE_PREFIX="wp_"
ENV WORDPRESS_DEBUG="false"
ENV WORDPRESS_CONFIG_EXTRA=

# WordPress optimization & feature configuration
ENV WORDPRESS_REDIS_HOST="redis"
ENV WORDPRESS_REDIS_PORT="6379"
ENV WORDPRESS_REDIS_DATABASE="0"
ENV WORDPRESS_REDIS_TIMEOUT="1"
ENV WORDPRESS_REDIS_READ_TIMEOUT="1"
# WORDPRESS_REDIS_PASSWORD and WORDPRESS_DB_PASSWORD passed via docker-compose .env only
ENV WORDPRESS_AUTO_UPDATE_CORE="minor"
ENV WORDPRESS_AUTO_UPDATE_PLUGINS="false"
ENV WORDPRESS_AUTO_UPDATE_THEMES="false"
ENV WORDPRESS_CONCATENATE_SCRIPTS="false"
ENV WORDPRESS_COMPRESS_SCRIPTS="false"
ENV WORDPRESS_COMPRESS_CSS="false"
ENV WORDPRESS_MEMORY_LIMIT="1024M"
ENV WORDPRESS_MAX_MEMORY_LIMIT="1536M"
ENV WORDPRESS_DISALLOW_FILE_EDIT="true"
ENV WORDPRESS_EMPTY_TRASH_DAYS="30"
ENV WORDPRESS_AUTO_SAVE_INTERVAL="300"

# Advanced Configuration
ENV WORDPRESS_CONTENT_URL=""
ENV WORDPRESS_CONTENT_DIR=""
ENV WORDPRESS_DB_QUERY_TIMEOUT="5"
ENV WORDPRESS_FORCE_SSL_ADMIN="true"
ENV WORDPRESS_FORCE_SSL_LOGIN="true"
ENV WORDPRESS_CACHE_KEY_SALT=""

# persistent dependencies
RUN set -eux; \
    apk add --no-cache \
    ghostscript \
    imagemagick \
    mysql-client \
    ;

# Install mysqli extension if not already present
RUN apk add --no-cache \
    postgresql-libs \
    sqlite-libs \
    libxml2 \
    oniguruma && \
    docker-php-ext-install -j "$(nproc)" mysqli

# Copy WordPress from official image
COPY --from=wp /usr/src/wordpress /var/www/html

# Add WordPress CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

COPY Caddyfile /etc/caddy/Caddyfile

# Copy custom PHP configuration
COPY conf.d/ /usr/local/etc/php/conf.d/

# Copy custom WordPress configuration
COPY wp-config-docker.php /var/www/html/wp-config.php

# Copy wp-content directory (plugins, themes, mu-plugins)
COPY wp-content/ /var/www/html/wp-content/

# Ensure proper permissions for WordPress and Caddy
RUN chown -R ${USER}:${USER} /var/www/html && \
    mkdir -p /data/caddy /config/caddy && \
    chown -R ${USER}:${USER} /data/caddy /config/caddy

WORKDIR /var/www/html

VOLUME /var/www/html/wp-content
VOLUME /config/caddy
VOLUME /data/caddy

COPY entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

USER ${USER}

EXPOSE 80 443

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
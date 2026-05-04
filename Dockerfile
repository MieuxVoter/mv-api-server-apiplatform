# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=7.4
ARG NGINX_VERSION=1.17


########################################################################################################################
# Prepare a base stage for our PHP needs (dev and prod).
FROM php:${PHP_VERSION}-fpm-alpine AS symfony_php_base

# Enable edge for font-noto-emoji@edge (@edge is not needed anymore)
#RUN apk add -X https://dl-cdn.alpinelinux.org/alpine/edge/main -u alpine-keys --allow-untrusted
#RUN echo "@edge http://dl-cdn.alpinelinux.org/alpine/edge/main" >> /etc/apk/repositories
#RUN echo "@edge http://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories
#RUN apk update

# Persistent / runtime deps
RUN apk add --no-cache \
        acl \
        bash \
        fcgi \
        file \
        font-noto \
        font-noto-emoji \
        gettext \
        git \
        jq \
        librsvg \
        make \
        ttf-dejavu \
        # SSH client can be needed to clone some PHP libs from github
        #openssh \
        # Fonts were used in the image generation of merit profiles I guess?
        #font-noto-cjk \
        #font-noto-extra \
        #terminus-font \
        #ttf-inconsolata \
        #ttf-font-awesome \
    ;

ARG APCU_VERSION=5.1.18

# Install build dependencies (that are deleted afterwards, except for phpext deps)
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
	    $PHPIZE_DEPS \
        autoconf \
        freetype-dev \
        g++ \
        make \
        icu-dev \
        imagemagick \
        imagemagick-libs \
        imagemagick-dev \
        libjpeg-turbo-dev \
        libtool \
        libpng-dev \
        libgomp \
        libxml2-dev \
        libzip-dev \
        oniguruma-dev \
        zlib-dev \
        # These php7-… packages were used in previous versions of alpine. \
        # Keeping them commented here until we know for sure we won't need them again.
#        php7-json \
#        php7-openssl \
#        php7-pdo \
#        php7-pdo_mysql \
#        php7-session \
#        php7-gd \
#        php7-simplexml \
#        php7-tokenizer \
#        php7-xml \
#        php7-imagick \
#        php7-pcntl \
#        php7-zip \
	; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-configure gd \
	    --with-freetype \
	; \
	docker-php-ext-install -j$(nproc) \
        gd \
        mbstring \
        mysqli \
        opcache \
        soap \
        intl \
        zip \
        pdo_mysql \
	; \
	pecl install \
	    apcu-${APCU_VERSION} \
        imagick \
	; \
	pecl clear-cache; \
	docker-php-ext-enable \
	    apcu \
	    imagick \
	    opcache \
	; \
	\
	runDeps="$( \
	    scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
	        | tr ',' '\n' \
	        | sort -u \
	        | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
	)"; \
	apk add --no-cache --virtual .phpexts-rundeps $runDeps; \
	\
	apk del .build-deps; \
    rm -rf /tmp/* /var/cache/apk/*

# Composer is the PHP package manager we use, let's grab it from another image.
# Right now this is composer 2, but composer 3 is on the way, and should be OK.
# I'm not sure we want to use `:latest` in here, tho.  Best update manually for now.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY docker/php/conf.d/symfony.ini $PHP_INI_DIR/conf.d/symfony.ini

RUN set -eux; \
	{ \
		echo '[www]'; \
		echo 'ping.path = /ping'; \
	} | tee /usr/local/etc/php-fpm.d/docker-healthcheck.conf

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV PATH="${PATH}:/root/.composer/vendor/bin"

# install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
# Disabled to try to bypass flex.symfony.com abandon -- perhaps safe to re-enable
#RUN set -eux; \
#	composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
#	composer clear-cache


WORKDIR /srv/app

#ARG APP_ENV=prod

# Allow using development versions of Symfony if needed
ARG STABILITY="stable"
ENV STABILITY ${STABILITY:-stable}

# Download the Symfony skeleton and leverage Docker cache layers
# This is clever ; let's try to prepare as much as we can before the COPY.
#ARG SYMFONY_VERSION="4"
#RUN composer create-project "symfony/skeleton ${SYMFONY_VERSION}" \
#    . \
#    --stability=$STABILITY --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
# But… Why would one want to clear the cache at this point?
#	composer clear-cache

# Note sure we do need this, since we're using Docker Compose
#VOLUME /srv/app/var

RUN mkdir -p \
      public \
      var \
      var/cache \
      var/logs \
    ;

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK \
    --start-period=15s \
    --interval=600s \
    --timeout=30s \
    --retries=2 \
    CMD ["docker-healthcheck"]

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


########################################################################################################################
FROM symfony_php_base AS symfony_php_dev

# We need this because our container user is root and we mount bind the dev's repo dir.
# This might cause trouble down the line if composer ever uses git to write anything.
RUN git config --global --add safe.directory /srv/app


########################################################################################################################
FROM symfony_php_base AS symfony_php_prod

# Copy the project files into the image
COPY . .

RUN --mount=type=cache,target=/root/.composer/cache \
    composer install \
      --no-dev \
      --prefer-dist \
      --no-progress \
      --no-scripts \
      --no-interaction \
    ; \
	composer clear-cache

RUN set -eux; \
	composer dump-autoload \
      --no-dev \
      --classmap-authoritative; \
	composer run-script \
      --no-dev \
      post-install-cmd; \
    sync;


########################################################################################################################
# "nginx" stage
# depends on the "php" stage above
FROM nginx:${NGINX_VERSION}-alpine AS symfony_nginx

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /srv/app

COPY --from=symfony_php_dev /srv/app/public public/


########################################################################################################################
# "h2-proxy-cert" stage
FROM alpine:latest AS symfony_h2-proxy-cert

RUN apk add --no-cache \
    openssl \
    ;

# Use this self-generated certificate only in dev, IT IS NOT SECURE!
RUN openssl genrsa -des3 -passout pass:NotSecure -out server.pass.key 2048
RUN openssl rsa -passin pass:NotSecure -in server.pass.key -out server.key
RUN rm server.pass.key
RUN openssl req -new -passout pass:NotSecure -key server.key -out server.csr \
	-subj '/C=SS/ST=SS/L=Gotham City/O=Symfony/CN=localhost'
RUN openssl x509 -req -sha256 -days 365 -in server.csr -signkey server.key -out server.crt


########################################################################################################################
### "h2-proxy" stage ; for dev (I don't use it, I use symfony serve and not docker)
FROM nginx:${NGINX_VERSION}-alpine AS symfony_h2-proxy

RUN mkdir -p /etc/nginx/ssl/
COPY --from=symfony_h2-proxy-cert server.key server.crt /etc/nginx/ssl/
COPY ./docker/h2-proxy/default.conf /etc/nginx/conf.d/default.conf



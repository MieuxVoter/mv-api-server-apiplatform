# The different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
# Note: some apk deps are hardcoded to php7 below
ARG PHP_VERSION=7.4
ARG NGINX_VERSION=1.17

########################################################################################################################
# "php" stage
FROM php:${PHP_VERSION}-fpm-alpine AS symfony_php

# Enable edge for font-noto-emoji
RUN apk add -X https://dl-cdn.alpinelinux.org/alpine/edge/main -u alpine-keys --allow-untrusted
RUN echo "@edge http://dl-cdn.alpinelinux.org/alpine/edge/main" >> /etc/apk/repositories
RUN echo "@edge http://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories
RUN apk update

# Persistent / runtime deps
RUN apk add --no-cache \
        acl \
        fcgi \
        file \
        gettext \
        git \
        jq \
        librsvg \
        ttf-dejavu \
        font-noto-emoji@adge \
#        font-noto \
#        font-noto-cjk \
#        font-noto-extra \
#        terminus-font \
#        ttf-inconsolata \
#        ttf-font-awesome \
    ;

ARG APCU_VERSION=5.1.18

# Build deps that are deleted except for phpext deps
RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
	    $PHPIZE_DEPS \
        autoconf \
        g++ \
        libtool \
        make \
        icu-dev \
        libzip-dev \
        freetype-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libxml2-dev \
        libgomp \
        imagemagick \
        imagemagick-libs \
        imagemagick-dev \
        oniguruma-dev \
        php7-json \
        php7-openssl \
        php7-pdo \
        php7-pdo_mysql \
        php7-session \
        php7-gd \
        php7-simplexml \
        php7-tokenizer \
        php7-xml \
        php7-imagick \
        php7-pcntl \
        php7-zip \
        zlib-dev \
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
# install Symfony Flex globally to speed up download of Composer packages (parallelized prefetching)
# Disabled to try to bypass flex.symfony.com abandon -- perhaps safe to re-enable
#RUN set -eux; \
#	composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
#	composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

WORKDIR /srv/app

# build for production
ARG APP_ENV=prod

# Allow to use development versions of Symfony
ARG STABILITY="stable"
ENV STABILITY ${STABILITY:-stable}

# Allow to select skeleton version
ARG SYMFONY_VERSION="4"

# Download the Symfony skeleton and leverage Docker cache layers
# This is clever ; let's try to prepare as much as we can before the COPY.
#RUN composer create-project "symfony/skeleton ${SYMFONY_VERSION}" \
#    . \
#    --stability=$STABILITY --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
# But… Why would one want to clear the cache at this point?
#	composer clear-cache

# Copy the project files into the image
COPY . .

# Re-enable the --no-dev once we don't need to debug prod.
# (the lies we tell ourselves…)
RUN composer install \
#    --no-dev \
    --prefer-dist --no-progress --no-scripts --no-interaction; \
	composer clear-cache

###> recipes ###
###< recipes ###

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload \
#	--no-dev \
	--classmap-authoritative; \
	composer run-script \
#	--no-dev \
	post-install-cmd; sync
VOLUME /srv/app/var

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=7200s --timeout=30s --retries=2 CMD ["docker-healthcheck"]

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


########################################################################################################################
# "nginx" stage
# depends on the "php" stage above
FROM nginx:${NGINX_VERSION}-alpine AS symfony_nginx

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /srv/app

COPY --from=symfony_php /srv/app/public public/


########################################################################################################################
# "h2-proxy-cert" stage
FROM alpine:latest AS symfony_h2-proxy-cert

RUN apk add --no-cache openssl

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



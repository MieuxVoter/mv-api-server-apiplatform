# the different stages of this Dockerfile are meant to be built into separate images
# https://docs.docker.com/develop/develop-images/multistage-build/#stop-at-a-specific-build-stage
# https://docs.docker.com/compose/compose-file/#target


# https://docs.docker.com/engine/reference/builder/#understand-how-arg-and-from-interact
ARG PHP_VERSION=7.4
ARG NGINX_VERSION=1.17

# "php" stage
FROM php:${PHP_VERSION}-fpm-alpine AS symfony_php

# persistent / runtime deps
RUN apk add --no-cache \
        acl \
        fcgi \
        file \
        gettext \
        git \
        jq \
    ;

ARG APCU_VERSION=5.1.18


#RUN apk add --update \
#        autoconf \
#        g++ \
#        libtool \
#        make \
#	    icu-dev \
#	    libzip-dev \
#	    zlib-dev \
#        freetype-dev \
#        libpng-dev \
#        libjpeg-turbo-dev \
#        libxml2-dev \
#        imagemagick \
#        imagemagick-dev \
#        oniguruma-dev \
#    && docker-php-ext-configure gd \
##        --with-gd \
##        --with-freetype-dir=/usr/include/ \
##        --with-png-dir=/usr/include/ \
##        --with-jpeg-dir=/usr/include/ \
#    && docker-php-ext-configure zip \
#    && docker-php-ext-install \
#        gd \
#        mbstring \
#        mysqli \
#        opcache \
#        soap \
#        intl \
#        zip \
#        pdo_mysql \
#    && pecl install apcu-${APCU_VERSION} \
#    && pecl install imagick \
#    && docker-php-ext-enable \
#	    apcu \
#	    imagick \
#	    opcache \
#    && apk del autoconf g++ libtool make \
#    && rm -rf /tmp/* /var/cache/apk/*

RUN set -eux; \
	apk add --no-cache --virtual .build-deps \
	    $PHPIZE_DEPS \
#	    icu-dev \
#	    libzip-dev \
#	    zlib-dev \
#	    imagemagick \
#	    imagemagick-dev \
        autoconf \
        g++ \
        libtool \
        make \
        icu-dev \
        libzip-dev \
        zlib-dev \
        freetype-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libxml2-dev \
        libgomp \
        imagemagick \
        imagemagick-libs \
        imagemagick-dev \
        oniguruma-dev \
#        git \
#        openssh-client \
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
#        sqlite \
	; \
	\
	docker-php-ext-configure zip; \
	docker-php-ext-configure gd; \
	docker-php-ext-install -j$(nproc) \
#	    intl \
#	    zip \
#		pdo_mysql \
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
	apk del .build-deps

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

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
RUN set -eux; \
	composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
	composer clear-cache
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
#RUN composer create-project "symfony/skeleton ${SYMFONY_VERSION}" \
#    . \
#    --stability=$STABILITY --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
#	composer clear-cache

RUN composer install \
    --prefer-dist --no-dev --no-progress --no-scripts --no-interaction; \
	composer clear-cache

###> recipes ###
###< recipes ###

COPY . .

RUN set -eux; \
	mkdir -p var/cache var/log; \
	composer dump-autoload --classmap-authoritative --no-dev; \
	composer run-script --no-dev post-install-cmd; sync
VOLUME /srv/app/var

COPY docker/php/docker-healthcheck.sh /usr/local/bin/docker-healthcheck
RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["docker-healthcheck"]

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]


# "nginx" stage
# depends on the "php" stage above
FROM nginx:${NGINX_VERSION}-alpine AS symfony_nginx

COPY docker/nginx/conf.d/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /srv/app

COPY --from=symfony_php /srv/app/public public/

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

### "h2-proxy" stage
FROM nginx:${NGINX_VERSION}-alpine AS symfony_h2-proxy

RUN mkdir -p /etc/nginx/ssl/
COPY --from=symfony_h2-proxy-cert server.key server.crt /etc/nginx/ssl/
COPY ./docker/h2-proxy/default.conf /etc/nginx/conf.d/default.conf

# Dockerfile
FROM symfony_php as symfony_php_dev

RUN echo "Done!"
#ARG XDEBUG_VERSION=2.8.0
#RUN set -eux; \
#	apk add --no-cache --virtual .build-deps $PHPIZE_DEPS; \
#	pecl install xdebug-$XDEBUG_VERSION; \
#	docker-php-ext-enable xdebug; \
#	apk del .build-deps
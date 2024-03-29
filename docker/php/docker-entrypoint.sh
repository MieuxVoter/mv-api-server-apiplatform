#!/bin/sh
set -e

echo "APP_ENV is $APP_ENV\n"

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-production"
	if [ "$APP_ENV" != 'prod' ]; then
		PHP_INI_RECOMMENDED="$PHP_INI_DIR/php.ini-development"
	fi
	ln -sf "$PHP_INI_RECOMMENDED" "$PHP_INI_DIR/php.ini"

    mkdir -p var/cache var/log

    # The first time volumes are mounted, the project needs to be recreated
    if [ ! -f composer.json ]; then
        composer create-project "symfony/skeleton $SYMFONY_VERSION" tmp --stability=$STABILITY --prefer-dist --no-progress --no-interaction
        jq '.extra.symfony.docker=true' tmp/composer.json > tmp/composer.tmp.json
        rm tmp/composer.json
        mv tmp/composer.tmp.json tmp/composer.json

        cp -Rp tmp/. .
        rm -Rf tmp/
    elif [ "$APP_ENV" != 'prod' ]; then
        composer --version
        # Unless we absolutely HAVE TO, let's not install composer deps twice.
        # The Dockerfile already handles those (for now)
        # We could manage to configure all this so that we seldom have to --build.
        # That would mean perhaps doing the composer install phase in here.
        #composer install --prefer-dist --no-progress --no-interaction
    fi

	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var
fi

echo "Starting PHP entrypoint…"

exec docker-php-entrypoint "$@"

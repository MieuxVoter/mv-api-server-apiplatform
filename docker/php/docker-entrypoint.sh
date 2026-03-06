#!/bin/sh
set -e

echo "APP_ENV is ${APP_ENV}"

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

  mkdir -p public var var/cache var/log

  # The first time volumes are mounted, the project needs to be recreated
  if [ ! -f composer.json ]; then
      composer create-project "symfony/skeleton $SYMFONY_VERSION" tmp --stability=$STABILITY --prefer-dist --no-progress --no-interaction
      jq '.extra.symfony.docker=true' tmp/composer.json > tmp/composer.tmp.json
      rm tmp/composer.json
      mv tmp/composer.tmp.json tmp/composer.json

      cp -Rp tmp/. .
      rm -Rf tmp/
  elif [ "$APP_ENV" != 'prod' ]; then
      echo "Running composer in dev env…"
      composer install --prefer-dist --no-progress --no-interaction --no-scripts
      composer dump-autoload --classmap-authoritative
      composer run-script post-install-cmd
  elif [ "$APP_ENV" = 'prod' ]; then
      echo "We're in prod !"
      composer --version
      # Unless we absolutely HAVE TO, let's not install composer deps twice.
      # The Dockerfile already handles those (for now)
      #composer install --prefer-dist --no-progress --no-interaction
  fi

	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var
fi

echo "Starting PHP entrypoint…"

exec docker-php-entrypoint "$@"

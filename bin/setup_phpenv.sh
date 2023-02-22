#!/usr/bin/env sh

# 1. Install phpenv
# 2. Run this

PHP_VERSION="7.4.33"

PHP_BUILD_CONFIGURE_OPTS="--with-gd --with-exif --with-mbstring --with-apcu --with-sodium --with-pgsql --with-pdo-pgsql --with-zip --with-json --with-xml --enable-fpm" \
	phpenv install $PHP_VERSION

# Defaults:
# --enable-sockets
# --enable-exif
# --with-zlib
# --with-zlib-dir=/usr
# --with-bz2
# --enable-intl
# --with-openssl
# --enable-soap
# --enable-xmlreader
# --with-xsl
# --enable-ftp
# --enable-cgi
# --with-curl=/usr
# --with-tidy
# --with-xmlrpc
# --enable-sysvsem
# --enable-sysvshm
# --enable-shmop
# --with-mysqli=mysqlnd
# --with-pdo-mysql=mysqlnd
# --with-pdo-sqlite
# --enable-pcntl
# --with-readline
# --enable-mbstring
# --disable-debug
# --enable-fpm
# --enable-bcmath
# --enable-phpdbg
# 
# See https://github.com/php-build/php-build/blob/17495066aa12cd9f74ef7acfe41191386741c9aa/share/php-build/default_configure_options

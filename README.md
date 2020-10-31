# Liquid Majority Judgment

This is a **REST API backend** for (Liquid) Majority Judgment.

It is **OpenApi v3 compliant**.
The Swagger (OpenApi v2) support is partial, it's hard to get both, so we chose to go with v3.

It supports:
- JSON
- LD+JSON
- HTML (sandbox)
- other formats can be added if needed

It features:
- An algorithmic constitution written in plain language
- 


## Install

PHP 7.2 and above, with quite a lot of extensions:
`ctype`, `iconv`, `json`, `mbstring`, `mysqlnd`, `sqlite3`, `xml`

What's `iconv` doing in here?

    apt install fortunes 

Get [Composer](https://getcomposer.org).

    composer install


### Via docker

Install [Docker Compose](https://docs.docker.com/compose/install/).

Run:

    docker-compose up


### Setup JWT

Run from root path:

    openssl genrsa -out config/jwt/private.pem -aes256 512

and write your private passphrase, without exotic characters.

Copy that passphrase inside `.env`:

    JWT_PASSPHRASE=passphrase


### References

* JWT : https://api-platform.com/docs/core/jwt/#jwt-authentication
* docker-compose : https://github.com/dunglas/symfony-docker
* Some others :
    * https://symfonycasts.com/screencast/api-platform-security/encode-user-password


## Browse generated doc

    bin/console server:run

Browse http://localhost:8000/api/docs


## Run the feature suite

You should do this, it's mesmerizing.  :]

First, copy `behat.yml.dist` to `behat.yml`:

    cp behat.yml.dist behat.yml

You do not need to edit it, but you may.
Then, run:

    vendor/bin/behat -vv

Useful dev options:

    vendor/bin/behat -vv --tags wip
    vendor/bin/behat -vv --rerun

Best mount `var/` to RAM first, for a 4x faster test-suite and to prevent your hard drives from premature aging.

    bin/tmpfs4var

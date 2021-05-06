# Majority Judgment OpenAPI Server with APIPlatform

<!-- We'll use a badge for this -->
**Demo**: https://oas.mieuxvoter.fr

This is a **REST API backend** for (Liquid) Majority Judgment.

It is **OpenApi v3 compliant**.
The Swagger (OpenApi v2) support is partial ; it's tricky to get both, so we chose to privilege v3.

It supports:
- JSON
- LD+JSON
- HTML (sandbox)
- other formats can be added as-needed

It features:
- Specifications written in plain language (french and english)
- A public endpoint to compute a ranking from a tally (todo)
- Authentication via a Json Web Token (JWT)
- Authenticated endpoints to create private polls and invitations to participate
- …


## ⚙ Install

### 🐋 Via docker

Install [Docker Compose](https://docs.docker.com/compose/install/).

Run:

    docker-compose up


### ✋ Manually

PHP 7.2 and above, with quite a lot of extensions:
`ctype`, `iconv`, `json`, `mbstring`, `mysqlnd`, `sqlite3`, `xml`

What's `iconv` doing in here?
Also, `sqlite3` is only useful in dev and test environments,
you should use `postgres` in production.

Get [Composer](https://getcomposer.org).

    composer install


### 🔐 Setup JWT

> php bin/console lexik:jwt:generate-keypair

#### Using a BASH script

    ./bin/setup_jwt.bash

#### Manually

Run from project's root path:

    openssl genrsa -out config/jwt/private.pem -aes256 2048
    openssl pkey -in config/jwt/private.pem --out config/jwt/public.pem -pubout

and write a private passphrase, without exotic characters (there are known issues).

Copy that passphrase inside `.env.local` *AND* `.env.test.local` (create the files):

    JWT_PASSPHRASE=passphrase_you_chose_above


### 🐉 Optionally

    apt install fortunes cowsay

To get positive reinforcement when the test-suite passes. 


### 📚 References

* JWT : https://api-platform.com/docs/core/jwt/#jwt-authentication
* docker-compose : https://github.com/dunglas/symfony-docker
* Some others :
    * https://symfonycasts.com/screencast/api-platform-security/encode-user-password


## 💃 Run, Doc, Sandbox

    bin/console server:run

Browse http://localhost:8000/

You may also use `0.0.0.0` to make the API available to your local area network (mobile testing),
as well as a custom port :

    bin/console server:run 0.0.0.0:8001

You may also use the `symfony` utility, if you have it:

    symfony serve --port 8000


## 🔍 Run the feature suite

_You should do this, it's mesmerizing._  :]

First, copy `behat.yml.dist` to `behat.yml`:

    cp behat.yml.dist behat.yml

You do not need to edit it, but you may.

> This step of copy will eventually be automated using composer hooks.
> That's a good first issue if you feel like it ;)


The features are in their own repositories,
so make sure you cloned the submodules as well:

    git submodule update --init --recursive

Then, run:

    ./vendor/bin/behat

Useful dev options:

    ./vendor/bin/behat -vv --tags wip
    ./vendor/bin/behat -vv --rerun

Best mount `var/` to RAM first, for a _4x_ faster test-suite and to prevent your hard drives from premature aging.

    ./bin/tmpfs4var.sh

## Generate Client Libraries

    make client-typescript-node 
    make client-php

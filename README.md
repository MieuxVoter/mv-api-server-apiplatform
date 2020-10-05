
# Liquid Majority Judgment

> You know it.  ♪  We want it.  ♬  HERE and NOW!  ♫


## Install

PHP 7.2 and above, with quite a lot of extensions:
`ctype`, `iconv`, `json`, `mbstring`, `mysqlnd`, `sqlite3`, `xml`

What's `iconv` doing in here?

    apt install fortunes 

Get [Composer](https://getcomposer.org).

    composer install


### Via docker

Install Docker Compose.  Run `doccker-compose up`.


### References

* JWT : https://api-platform.com/docs/core/jwt/#jwt-authentication
* docker-compose : https://github.com/dunglas/symfony-docker
* Some others :
    * https://symfonycasts.com/screencast/api-platform-security/encode-user-password


## Browse generated doc

    bin/console server:run

Browse http://localhost:8000/api/docs


## Run the feature suite

    vendor/bin/behat -vv
    vendor/bin/behat -vv --tags wip
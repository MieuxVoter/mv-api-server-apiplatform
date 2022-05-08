
We configure our _generators_, make them read our _spec_, and enjoy the _generated_ codebases.

## Docs

- https://github.com/OpenAPITools/openapi-generator/blob/master/docs/customization.md
- …

## Usage

    make client-typescript-node
    make client-php

Add more as-meeded !


## Dump templates to copy them before override

Example for `php` :

    java -jar openapi-generator-cli.jar author template --output /tmp/php-templates -g php
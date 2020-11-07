#!/usr/bin/env bash

# Example usage:
#     $ VERSION=0.1.0 bin/dumpoas.sh
# We should get the version from git…
# git describe --tags --abbrev=0 --always

VERSION=${VERSION:-0.0.0}
SPEC_FILENAME=./openapi/spec/mvapi.${VERSION}

bin/console api:openapi:export --spec-version=3 > ${SPEC_FILENAME}.oas3.json
bin/console api:openapi:export --spec-version=3 --yaml > ${SPEC_FILENAME}.oas3.yaml

echo "Generated OpenAPI v3 spec in `pwd`/${SPEC_FILENAME}.oas3.json"
echo "Generated OpenAPI v3 spec in `pwd`/${SPEC_FILENAME}.oas3.yaml"

bin/console api:openapi:export --spec-version=2 > ${SPEC_FILENAME}.oas2.json
bin/console api:openapi:export --spec-version=2 --yaml > ${SPEC_FILENAME}.oas2.yaml

echo "Generated OpenAPI v2 spec in `pwd`/${SPEC_FILENAME}.oas2.json"
echo "Generated OpenAPI v2 spec in `pwd`/${SPEC_FILENAME}.oas2.yaml"

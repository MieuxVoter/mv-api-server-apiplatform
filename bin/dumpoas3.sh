#!/usr/bin/env bash

# Example usage:
#     $ VERSION=0.1.0 bin/dumpoas3.sh
# We should get the version from git…
# git describe --tags --abbrev=0 --always

VERSION=${VERSION:-0.0.0}
SPEC_FILENAME=./openapi/spec/mvapi.${VERSION}.oas3

bin/console api:openapi:export --spec-version=3 > ${SPEC_FILENAME}.json
bin/console api:openapi:export --spec-version=3 --yaml > ${SPEC_FILENAME}.yaml

echo "Generated OpenAPI v3 spec in `pwd`/${SPEC_FILENAME}.json"
echo "Generated OpenAPI v3 spec in `pwd`/${SPEC_FILENAME}.yaml"

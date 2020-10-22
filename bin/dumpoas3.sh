#!/usr/bin/env bash

OAS_FILENAME="${OAS_FILENAME:-jlm.oas3.json}"

bin/console api:openapi:export --spec-version=3 > $OAS_FILENAME

echo "Generated OpenAPI v3 spec in `pwd`/$OAS_FILENAME"

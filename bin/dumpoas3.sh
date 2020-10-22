#!/usr/bin/env sh

bin/console api:openapi:export --spec-version=3 > jlm.json

echo "Generated OpenAPI v3 spec in `pwd`/jlm.oas3.json"

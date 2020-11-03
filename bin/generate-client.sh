#!/bin/env sh

TARGET="php"
NAME=MjOpenApi
GIT_HOST=framagit.org
USER_ID=limaju
REPO_ID=limaju-client-lib-${TARGET}
OUTPUT_DIRECTORY=/tmp/${REPO_ID}
#OUTPUT_DIRECTORY=~/code/snd/${REPO_ID}
VERSION=0.0.0
SPEC_FILENAME=./openapi/spec/mvapi.${VERSION}.oas3

###

bin/console api:openapi:export --spec-version=3 > ${SPEC_FILENAME}.json
bin/console api:openapi:export --spec-version=3 --yaml > ${SPEC_FILENAME}.yaml

###

# install: https://openapi-generator.tech/
npx @openapitools/openapi-generator-cli generate \
    --config ./openapi/generator/config.yml \
    --template-dir ./openapi/generator/${TARGET}-templates \
    --input-spec ${SPEC_FILENAME}.json \
    --generator-name ${TARGET} \
    --output ${OUTPUT_DIRECTORY} \
    --git-host ${GIT_HOST} \
    --git-user-id ${USER_ID} \
    --git-repo-id ${REPO_ID}


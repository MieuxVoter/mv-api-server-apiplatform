#!/bin/env bash

TARGET="php"
#TARGET="typescript-node"
#TARGET="mathematica"
NAME=MvApi
GIT_HOST=github.com
USER_ID=MieuxVoter
REPO_PREFIX=mv-api-client-lib
OUTPUT_DIRECTORY=/tmp

VERSION=0.0.0
SPEC_FILEPATH_NOEXT=./openapi/spec/mvapi.${VERSION}.oas3


###
# https://unix.stackexchange.com/questions/31414/how-can-i-pass-a-command-line-argument-into-a-shell-script/505342#505342

helpFunction()
{
   echo ""
   echo "Usage: $0 -t TARGET -o OUTPUT_DIRECTORY"
   echo -e "\t-t Target language, such as php or typoscript-node.  Defaults to php."
   echo -e "\t-o Output directory.  Defaults to /tmp"
   exit 1
}

while getopts "t:o:" opt
do
   case "$opt" in
      t ) TARGET="$OPTARG" ;;
      o ) OUTPUT_DIRECTORY="$OPTARG" ;;
      ? ) helpFunction ;;
   esac
done


###

REPO_ID=${REPO_PREFIX}-${TARGET}
LIBRARY_OUTPUT_DIRECTORY=${OUTPUT_DIRECTORY}/${REPO_ID}
TEMPLATE_DIRECTORY=./openapi/generator/${TARGET}-templates

###


bin/console api:openapi:export --spec-version=3 > ${SPEC_FILEPATH_NOEXT}.json
bin/console api:openapi:export --spec-version=3 --yaml > ${SPEC_FILEPATH_NOEXT}.yaml

###

# In this directory we may override the mustache templates
mkdir -p ${TEMPLATE_DIRECTORY}

# install: https://openapi-generator.tech/
#GENERATOR=npx @openapitools/openapi-generator-cli

# or
# wget https://repo1.maven.org/maven2/org/openapitools/openapi-generator-cli/5.1.0/openapi-generator-cli-5.1.0.jar -O openapi-generator-cli.jar
GENERATOR="java -jar ./openapi-generator-cli.jar"

${GENERATOR} generate \
    --skip-validate-spec \
    --config ${TEMPLATE_DIRECTORY}/generator-config.yml \
    --template-dir ${TEMPLATE_DIRECTORY} \
    --input-spec ${SPEC_FILEPATH_NOEXT}.json \
    --generator-name ${TARGET} \
    --output ${LIBRARY_OUTPUT_DIRECTORY} \
    --git-host ${GIT_HOST} \
    --git-user-id ${USER_ID} \
    --git-repo-id ${REPO_ID}


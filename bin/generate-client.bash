#!/bin/env bash

# For a list of available targets, and further documpentation, see
# https://github.com/OpenAPITools/openapi-generator

# Bunch of params to configure or move to args or env
NAME=MvApi
GIT_HOST=github.com
# Lowercase is preferred
USER_ID=mieuxvoter
REPO_PREFIX=mv-api-client-lib
DEFAULT_OUTPUT_DIRECTORY=./openapi/generated
# Overrides the values in dotenv for MVOAS_SERVER (it's localhost for me in dev)
SERVER="https://oas.mieuxvoter.fr"
# This is set by -t TARGET
TARGET="php"
# This is set by -o DIR
OUTPUT_DIRECTORY=${DEFAULT_OUTPUT_DIRECTORY}

# todo: fetch version from git describe once it's meaningful
VERSION=0.0.0
SPEC_FILEPATH_NOEXT=./openapi/spec/mvapi.${VERSION}.oas3


###
# https://unix.stackexchange.com/questions/31414/how-can-i-pass-a-command-line-argument-into-a-shell-script/505342#505342

helpFunction()
{
   echo ""
   echo "Usage: $0 -t TARGET -o OUTPUT_DIRECTORY"
   echo -e "\t-t Target language, such as php or typoscript-node.  Defaults to php."
   echo -e "\t-o Output directory.  Defaults to ${DEFAULT_OUTPUT_DIRECTORY}"
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

# Override the MVOAS_SERVER value from dotenv, since it's going to be localhost
MVOAS_SERVER=$SERVER bin/console api:openapi:export --spec-version=3 > ${SPEC_FILEPATH_NOEXT}.json
MVOAS_SERVER=$SERVER bin/console api:openapi:export --spec-version=3 --yaml > ${SPEC_FILEPATH_NOEXT}.yaml

###


mkdir -p ${OUTPUT_DIRECTORY}

# In this directory we may override the mustache templates
mkdir -p ${TEMPLATE_DIRECTORY}

# install: https://openapi-generator.tech/
#GENERATOR=npx @openapitools/openapi-generator-cli
# or
# wget https://repo1.maven.org/maven2/org/openapitools/openapi-generator-cli/5.1.0/openapi-generator-cli-5.1.0.jar -O openapi-generator-cli.jar
GENERATOR="java -jar ./openapi-generator-cli.jar"

GENERATOR_PARAMS=(
    --skip-validate-spec
    --config ${TEMPLATE_DIRECTORY}/generator-config.yml
    --template-dir ${TEMPLATE_DIRECTORY}
    --input-spec ${SPEC_FILEPATH_NOEXT}.json
    --generator-name ${TARGET}
    --output ${LIBRARY_OUTPUT_DIRECTORY}
    --git-host ${GIT_HOST}
    --git-user-id ${USER_ID}
    --git-repo-id ${REPO_ID}
)

${GENERATOR} generate ${GENERATOR_PARAMS[@]}

#!/usr/bin/env bash

# Run this to set up the Json Web Token

# Make sure we're in project root directory
cd $(cd -P -- "$(dirname -- "$0")" && pwd -P)
cd ..

# Scope things into a function to avoid showing the password to other processes
generate_jwt() {
    local PASSPHRASE=
    echo -ne "Enter (long!) passphrase for private key:\n"
    echo -ne "(do not use any special characters, there are known issues)\n"
    echo -ne "?\n"
    read -s PASSPHRASE
    #echo $PASSPHRASE

    # Not 100% sure 2048 bits keys are legal in France ; EAFP ; 1024 is considered weak nowadays
    openssl genrsa -out ./config/jwt/private.pem -passout pass:${PASSPHRASE} -aes256 2048
    echo -e "Wrote private key to ./config/jwt/private.pem"
    openssl pkey -in ./config/jwt/private.pem -passin pass:${PASSPHRASE} --out ./config/jwt/public.pem -pubout
    echo -e "Wrote public key to ./config/jwt/public.pem"

    echo "JWT_PASSPHRASE=${PASSPHRASE}" >> .env.local
    echo -e "Wrote passphrase to .env.local"
    echo "JWT_PASSPHRASE=${PASSPHRASE}" >> .env.test.local
    echo -e "Wrote passphrase to .env.test.local"
}

generate_jwt

echo -ne "Done!"

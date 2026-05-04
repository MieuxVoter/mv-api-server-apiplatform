# Json Web Token Encryption Keys

The `public.pem.dist` and `private.pem.dist` files are only here for the test suite running in the CI.

**You should NOT use them.**

If you've setup JWT correctly, using `make install` or `bash bin/setup_jwt.bash` for example, you should have two more files: `public.pem` and `private.pem`.

These files should be ignored by git, and their passphrase (that you chose during their generation) should be set in your local environment variable `JWT_PASSPHRASE`.

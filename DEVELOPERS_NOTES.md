## Mount `var/` in RAM

If you run the suite a lot like I do

    bin/tmpfs4var


## Upgrading to newest ApiPlatform

We're going to need to swap out the SwaggerDocumentationDecorator

> Using the swagger DocumentationNormalizer is deprecated in favor of decorating the OpenApiFactory, use the "openapi.backward_compatibility_layer" configuration to change this behavior.

We're also going to have issues with `{uuid}` instead of `{id}` in routes.


## Troubleshooting

> Unable to create a signed JWT from the given configuration.

Try to regenerate your JWT keypair:
    bin/setup_jwt.bash

And then check your `.env[.test].local` files to ensure they are well configured.

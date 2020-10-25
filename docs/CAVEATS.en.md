
## `\n` suffix in the PHP docblocks

Sometimes in the annotations the lines will be suffixed by `\n`.
This is to pass multiple lines to ApiPlatform's generated `description` field in the OpenApi spec.

## camelCase usage in properties

Use `camelCase` in the Entities' attributes.

`snake_case` won't be understood by ApiPlatform.

`slug-case` names for things that are variables is usually not allowed (`-` means subtract).


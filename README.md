# Masilia Flex recipes

[Symfony Flex](https://symfony.com/doc/current/setup/flex.html) recipes for the [Masilia packages for Ibexa](https://github.com/MasiliaLabs): on `composer require`, they enable the bundle, write its route file and its configuration skeleton, and print what is left to do (migrations, admin build, policies).

| Package | Recipe | What it does |
| --- | --- | --- |
| `masilia/ibexa-admin-kit` | 0.1 | enables the bundle |
| `masilia/ibexa-sites` | 0.2 | bundle, routes |
| `masilia/ibexa-ai` | 2.3 | bundle, routes, `encryption_key` setting |
| `masilia/ibexa-ai-editor` | 0.2 | bundle, routes |
| `masilia/ibexa-ai-craft` | 2.2 | bundle |
| `masilia/ibexa-blocks` | 0.5 | bundle, routes (loaded before Ibexa's), settings skeleton |
| `masilia/ibexa-icons` | 0.1 | bundle, routes |
| `masilia/editorial-toolbox` | 0.3 | bundle, routes (`/api`), settings |

## Use them

Add the endpoint of this repository to the `composer.json` of your project, after the one of Ibexa:

```json
"extra": {
    "symfony": {
        "endpoint": [
            "https://api.github.com/repos/ibexa/recipes/contents/index.json?ref=flex/main",
            "https://api.github.com/repos/MasiliaLabs/recipes/contents/index.json?ref=flex/main",
            "flex://defaults"
        ]
    }
}
```

Then `composer require masilia/ibexa-blocks` (and the others) runs the recipe. Recipes cannot do everything: the npm dependencies, the migrations and the build of the admin stay yours; each recipe prints the commands.

## Layout and build

```
masilia/<package>/<version>/
    manifest.json        bundles, copy-from-recipe
    post-install.txt     what is printed after the install
    config/...           files copied to the project
bin/build.php            compiles them into dist/ (index.json + one json per recipe)
```

`php bin/build.php [base-url]` writes `dist/`. **Publishing is automatic**: a push to `main` runs the *Build* workflow, which compiles the recipes and replaces the `flex/main` branch (the one Flex downloads) with `dist/`. To try them locally, serve `dist/` over HTTP (`php -S 127.0.0.1:8099 -t dist`), compile with that `base-url` (`php bin/build.php http://127.0.0.1:8099`) and list `http://127.0.0.1:8099/index.json` in `extra.symfony.endpoint` (with `config.secure-http` set to `false`).

A recipe version applies to the package versions from that one on (`0.5` for `ibexa-blocks` 0.5.x and later, until a newer recipe exists). Add a new directory when a release changes what the recipe should do.

## License

MIT, copyright © 2026 MasiliaLabs. Independent project, not affiliated with or endorsed by Ibexa AS.

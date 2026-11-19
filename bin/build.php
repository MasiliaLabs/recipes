#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Compiles the recipes into what Symfony Flex downloads from an endpoint:
 *   dist/index.json                                  the recipes and where to find them
 *   dist/<vendor>.<package>.<version>.json          one recipe: manifest, files, ref
 *
 * Usage: php bin/build.php [base-url]
 *   base-url  where dist/ will be served; default: the flex/main branch of github.com/MasiliaLabs/recipes
 */

$base = rtrim($argv[1] ?? 'https://raw.githubusercontent.com/MasiliaLabs/recipes/flex/main', '/');
$root = dirname(__DIR__);
$dist = $root . '/dist';
if (is_dir($dist)) {
    foreach (glob($dist . '/*.json') ?: [] as $old) {
        unlink($old);
    }
} else {
    mkdir($dist, 0775, true);
}

$index = [];
foreach (glob($root . '/masilia/*/*/manifest.json') ?: [] as $manifestFile) {
    $dir = dirname($manifestFile);
    $version = basename($dir);
    $package = 'masilia/' . basename(dirname($dir));

    $manifest = json_decode((string) file_get_contents($manifestFile), true, 512, JSON_THROW_ON_ERROR);
    $postInstall = $dir . '/post-install.txt';
    if (is_file($postInstall)) {
        $manifest['post-install-output'] = explode("\n", rtrim((string) file_get_contents($postInstall), "\n") . "\n");
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $relative = substr($file->getPathname(), strlen($dir) + 1);
        if (in_array($relative, ['manifest.json', 'post-install.txt'], true)) {
            continue;
        }
        $files[$relative] = ['contents' => explode("\n", rtrim((string) file_get_contents($file->getPathname()), "\n") . "\n"), 'executable' => is_executable($file->getPathname())];
    }
    ksort($files);

    $ref = sha1(json_encode([$manifest, $files], JSON_THROW_ON_ERROR));
    $recipe = ['manifests' => [$package => ['manifest' => $manifest, 'files' => $files, 'ref' => $ref]]];
    file_put_contents(sprintf('%s/%s.%s.json', $dist, str_replace('/', '.', $package), $version), json_encode($recipe, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
    $index[$package][] = $version;
    printf("%s %s (%s)\n", $package, $version, substr($ref, 0, 8));
}
ksort($index);

file_put_contents($dist . '/index.json', json_encode([
    'aliases' => new stdClass(),
    'recipe-conflicts' => [],
    'versions' => [],
    'branch' => 'main',
    'is_contrib' => false,
    'recipes' => $index,
    '_links' => [
        'repository' => 'github.com/MasiliaLabs/recipes',
        'origin_template' => '{package}:{version}@github.com/MasiliaLabs/recipes:main',
        'recipe_template' => $base . '/{package_dotted}.{version}.json',
        'recipe_template_relative' => '{package_dotted}.{version}.json',
        'archived_recipes_template' => $base . '/archived/{package_dotted}/{ref}.json',
        'archived_recipes_template_relative' => 'archived/{package_dotted}/{ref}.json',
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
echo "dist/index.json written\n";

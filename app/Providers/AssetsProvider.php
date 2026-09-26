<?php

declare(strict_types=1);

namespace Iniznet\Kumki\Providers;

use Iniznet\Kumki\Support\ClassResolver;
use Iniznet\Kumki\Support\FileManifest;
use Iniznet\Kumki\Support\PluginPaths;
use Iniznet\Mahout\Assets\AssetsConfig;
use Iniznet\Mahout\Assets\DevMode;
use Iniznet\Mahout\Assets\EntryList;
use Iniznet\Mahout\Assets\Exception\EntryPointMalformed;
use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Contracts\ServiceProvider;

/**
 * Declares the two build artifacts the asset pipeline consumes: the manifest and
 * the classmap. The package's own AssetsProvider, named after this one in the
 * composition root, reads the config and attaches the enqueue hooks.
 *
 * The entry list comes from config/entries.php, the one explicit list; nothing scans
 * the filesystem and nothing globs. The difference from the theme's provider is one
 * line — the base URL is the plugin's own build directory rather than the theme's —
 * which is the whole reason the asset path belongs to the host and not to the
 * package.
 */
final class AssetsProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        $paths = $container->get(PluginPaths::class);

        $container->set(ClassResolver::fromClassmapFile($paths->path('build/classmap.json')));

        $declarations = require $paths->path('config/entries.php');

        if (!\is_array($declarations)) {
            throw EntryPointMalformed::notADeclaration(\get_debug_type($declarations));
        }

        $container->set(new AssetsConfig(
            manifest: new FileManifest($paths->path('build/manifest.json')),
            entries: EntryList::fromDeclarations($declarations),
            baseUrl: $paths->buildUrl(),
            devMode: DevMode::fromConstant(),
        ));
    }

    public function boot(Container $container): void
    {
        // The package's AssetsProvider attaches the enqueue hooks; the config above
        // is its only input. Nothing is attached here.
    }
}

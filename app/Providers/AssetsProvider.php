<?php

declare(strict_types=1);

namespace Iniznet\Kumki\Providers;

use Iniznet\Kumki\Exception\InvalidEntryDeclaration;
use Iniznet\Kumki\Support\ClassResolver;
use Iniznet\Kumki\Support\FileManifest;
use Iniznet\Kumki\Support\PluginPaths;
use Iniznet\Mahout\Assets\AssetsConfig;
use Iniznet\Mahout\Assets\DevMode;
use Iniznet\Mahout\Assets\EntryList;
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
            throw InvalidEntryDeclaration::forType(\get_debug_type($declarations));
        }

        // Each entry is validated as a map and no further: the handle, the source and
        // the context are the assets package's rules, and it reports their breach with
        // its own exception. This host answers only for the shape of its own file.
        $entries = [];

        foreach ($declarations as $entry) {
            $entries[] = self::entry($entry);
        }

        $container->set(new AssetsConfig(
            manifest: new FileManifest($paths->path('build/manifest.json')),
            entries: EntryList::fromDeclarations($entries),
            baseUrl: $paths->buildUrl(),
            devMode: DevMode::fromConstant(),
        ));
    }

    /**
     * One entry, validated as a map of string keys to mixed values — the shape
     * `config/entries.php` documents. The keys inside it are the package's business:
     * an entry with no source is `EntryPointMalformed::missingSource()`, raised where
     * handles and sources are actually understood. What is this host's business is that
     * the file it read is a list of maps, because nothing downstream can say that later
     * without a cast, and a cast is this file agreeing to be wrong quietly.
     *
     * @return array<string, mixed>
     */
    private static function entry(mixed $entry): array
    {
        if (!\is_array($entry)) {
            throw InvalidEntryDeclaration::forEntry(\get_debug_type($entry));
        }

        $map = [];

        foreach ($entry as $key => $value) {
            if (!\is_string($key)) {
                throw InvalidEntryDeclaration::forEntry('a map with a '.\get_debug_type($key).' key');
            }

            $map[$key] = $value;
        }

        return $map;
    }

    public function boot(Container $container): void
    {
        // The package's AssetsProvider attaches the enqueue hooks; the config above
        // is its only input. Nothing is attached here.
    }
}

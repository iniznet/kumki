<?php

/**
 * The composition root.
 *
 * Every provider is named here, in order, and nowhere else. There is no config file
 * between this list and the registrations it produces.
 *
 * This is the site's root of record: `Kernel::inWordPress(self::class)` claims the
 * process, so a second host that also installed the packages is refused rather than
 * silently served this one's classes (mahout-kernel ADR-0007). A plugin is loaded
 * before the theme, which is why the claim matters here in a way it does not in a
 * theme: the installation that boots first is the one everything else resolves
 * against.
 */

declare(strict_types=1);

namespace Iniznet\Kumki;

use Iniznet\Kumki\Exception\NotBooted;
use Iniznet\Kumki\Features\Offices\OfficesModule;
use Iniznet\Kumki\Providers\AdminProvider;
use Iniznet\Kumki\Providers\AssetsProvider;
use Iniznet\Kumki\Providers\ContentProvider;
use Iniznet\Kumki\Providers\EditorProvider;
use Iniznet\Kumki\Support\PluginPaths;
use Iniznet\Mahout\Assets\AssetsProvider as AssetsPackageProvider;
use Iniznet\Mahout\Content\ContentProvider as ContentPackageProvider;
use Iniznet\Mahout\Db\DbProvider;
use Iniznet\Mahout\Db\MigrationRunner;
use Iniznet\Mahout\Fields\FieldsProvider;
use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Kernel;

final class Bootstrap
{
    private static ?Kernel $kernel = null;

    /**
     * Boot the plugin exactly once, and register the migration run against this
     * file's activation.
     *
     * The theme's equivalent is `after_switch_theme`, which mahout-db attaches
     * because a theme has no activation hook; a plugin does have one, so the first
     * install runs on activation instead of waiting for the lazy admin path. The
     * runner is resolved from the booted graph, so this adds no owner of its own.
     */
    public static function run(string $pluginFile): void
    {
        $kernel = Kernel::inWordPress(self::class);

        // The main plugin file is the only thing that can name this installation's
        // own URLs: core's plugin_dir_url() takes a file, not a directory, so the
        // path is injected once here rather than re-derived by every provider that
        // needs one and gets the level wrong.
        $kernel->service(new PluginPaths($pluginFile));

        $kernel->provider(AssetsProvider::class);
        $kernel->provider(AssetsPackageProvider::class);
        $kernel->provider(DbProvider::class);
        $kernel->provider(FieldsProvider::class);
        $kernel->provider(ContentPackageProvider::class);
        $kernel->provider(ContentProvider::class);
        $kernel->provider(EditorProvider::class);
        $kernel->provider(AdminProvider::class);

        // A module boots after every provider, so it can resolve the contracts its
        // feature reads by id. The worked example lives in app/Features/Offices.
        $kernel->module(OfficesModule::class);

        $kernel->boot();

        \register_activation_hook(
            $pluginFile,
            static function () use ($kernel): void {
                $kernel->services()->get(MigrationRunner::class)->migrate();
            },
        );

        self::$kernel = $kernel;
    }

    /**
     * The declared service graph, resolved by the keys the composition root
     * registered them under.
     */
    public static function services(): Container
    {
        return self::kernel()->services();
    }

    private static function kernel(): Kernel
    {
        if (!self::$kernel instanceof Kernel) {
            throw NotBooted::beforeResolution();
        }

        return self::$kernel;
    }
}

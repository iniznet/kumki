<?php

/**
 * The field seam.
 *
 * Three declarations and one registration, and each is here rather than in a package
 * for a stated reason.
 *
 * The panels and the option screens are read from `config/fields.php` and
 * `config/display-options.php`, validated to the shapes those files promise, and bound
 * under the package's `Panels` and `OptionScreens` contracts. The field package's admin
 * provider then attaches the metaboxes, the save entry, the field route, the
 * write-failure notice and the settings pages that the declarations imply — and attaches
 * none of them when a declaration is empty. So this plugin declares fields; it does not
 * write admin code, which is the whole arrangement.
 *
 * The query builder is declared here because no package binds it: `FieldQuery` needs the
 * two value tables, and only a host knows it wants them at all. It is bound under the
 * contract its consumers name, so a repository states a dependency instead of reaching
 * for a container.
 *
 * The groups themselves reach the registry on the package's own hook, in the pass the
 * package runs for that purpose: a group registered at boot time would arrive after the
 * admin screen had already asked for it.
 *
 * The stylesheet URL is this host's to name — `PluginPaths` holds the main plugin file,
 * which is what `plugin_dir_url()` needs. Passing nothing would make the package resolve
 * the asset through `get_theme_file_path()`, which is a path a plugin cannot name.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Providers;

use Iniznet\Kumki\Exception\InvalidFieldDeclaration;
use Iniznet\Kumki\Features\Fields\DeclaredOptionScreens;
use Iniznet\Kumki\Features\Fields\DeclaredPanels;
use Iniznet\Kumki\Support\PluginPaths;
use Iniznet\Mahout\Db\Contracts\SqlConnection;
use Iniznet\Mahout\Fields\Admin\FieldStyles;
use Iniznet\Mahout\Fields\Contracts\FieldQuery as FieldQueryContract;
use Iniznet\Mahout\Fields\Contracts\FieldRegistry;
use Iniznet\Mahout\Fields\Contracts\OptionScreens as OptionScreensContract;
use Iniznet\Mahout\Fields\Contracts\Panels;
use Iniznet\Mahout\Fields\FieldLeavesTable;
use Iniznet\Mahout\Fields\FieldPanel;
use Iniznet\Mahout\Fields\FieldQuery;
use Iniznet\Mahout\Fields\FieldValuesTable;
use Iniznet\Mahout\Fields\Hooks as FieldHooks;
use Iniznet\Mahout\Fields\OptionScreen;
use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Contracts\ServiceProvider;

final class EditorProvider implements ServiceProvider
{
    /** @var list<FieldPanel> */
    private array $panels = [];

    public function register(Container $container): void
    {
        $paths = $container->get(PluginPaths::class);

        $this->panels = self::panels(require $paths->path('config/fields.php'));
        $screens = self::screens(require $paths->path('config/display-options.php'));

        $collection = new DeclaredPanels($this->panels);

        $container->set($collection, Panels::class);
        $container->set(new DeclaredOptionScreens($screens), OptionScreensContract::class);

        $container->set(new FieldStyles(
            url: $paths->url('resources/fields.css'),
            scriptUrl: $paths->url('resources/fields.js'),
            panels: $collection,
            screens: new DeclaredOptionScreens($screens),
        ));

        $connection = $container->get(SqlConnection::class);

        $container->set(service: new FieldQuery(
            $container->get(FieldRegistry::class),
            $connection,
            FieldValuesTable::table($connection->prefix(), $connection->charsetCollate()),
            FieldLeavesTable::table($connection->prefix(), $connection->charsetCollate()),
        ), id: FieldQueryContract::class);

        // Attached during register, not boot: FieldsProvider's boot fires this hook, and
        // a listener added after it would never run. The groups reach the registry in
        // the pass the package reserves for exactly that.
        \add_action(
            FieldHooks::REGISTRY_LOADED,
            function (FieldRegistry $registry): void {
                foreach ($this->panels as $panel) {
                    $registry->register($panel->group);
                }
            },
            priority: 10,
            accepted_args: 1,
        );
    }

    public function boot(Container $container): void
    {
        // Nothing here: the registry listener is attached in register(), because every
        // provider registers before any provider boots and the field package fires its
        // registry hook from its own boot pass.
    }

    /**
     * @return list<FieldPanel>
     */
    private static function panels(mixed $declarations): array
    {
        if (!\is_array($declarations)) {
            throw InvalidFieldDeclaration::forType(\get_debug_type($declarations));
        }

        foreach ($declarations as $declaration) {
            if (!$declaration instanceof FieldPanel) {
                throw InvalidFieldDeclaration::forType(\get_debug_type($declaration));
            }
        }

        return \array_values($declarations);
    }

    /**
     * @return list<OptionScreen>
     */
    private static function screens(mixed $declarations): array
    {
        if (!\is_array($declarations)) {
            throw InvalidFieldDeclaration::forOptionScreenType(\get_debug_type($declarations));
        }

        foreach ($declarations as $declaration) {
            if (!$declaration instanceof OptionScreen) {
                throw InvalidFieldDeclaration::forOptionScreenType(\get_debug_type($declaration));
            }
        }

        return \array_values($declarations);
    }
}

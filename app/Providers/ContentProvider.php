<?php

declare(strict_types=1);

namespace Iniznet\Kumki\Providers;

use Iniznet\Kumki\Exception\InvalidContentDeclaration;
use Iniznet\Kumki\Support\Hooks;
use Iniznet\Kumki\Support\PluginPaths;
use Iniznet\Mahout\Content\Contracts\Registrar;
use Iniznet\Mahout\Content\PostReader;
use Iniznet\Mahout\Content\PostType;
use Iniznet\Mahout\Content\RestRoute;
use Iniznet\Mahout\Content\Taxonomy;
use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Contracts\ServiceProvider;

/**
 * Registers the declared content model on init, through the content package's
 * Contracts surface. The declarations live in config/content-types.php, the one
 * declarative list; nothing is registered at file scope and nothing is discovered.
 *
 * This is the provider the theme has no equivalent of, because it is where the
 * boundary between the two hosts actually falls: a post type declared here survives a
 * theme switch, and one declared in a theme does not.
 */
final class ContentProvider implements ServiceProvider
{
    /** @var list<PostType|Taxonomy|RestRoute> */
    private array $declarations = [];

    public function register(Container $container): void
    {
        // Declared in register(), not boot(): a feature's module resolves its collaborators
        // during its own register pass, and the kernel's order is providers register,
        // modules register, providers boot, modules boot. A service declared in a boot
        // pass is invisible to every module in the process.
        $container->set(new PostReader());

        $declarations = require $container->get(PluginPaths::class)->path('config/content-types.php');

        if (!\is_array($declarations)) {
            throw InvalidContentDeclaration::forShape(\sprintf('the file returned %s', \get_debug_type($declarations)));
        }

        foreach ($declarations as $declaration) {
            if (!$declaration instanceof PostType
                && !$declaration instanceof Taxonomy
                && !$declaration instanceof RestRoute
            ) {
                throw InvalidContentDeclaration::forType(\get_debug_type($declaration));
            }

            $this->declarations[] = $declaration;
        }
    }

    public function boot(Container $container): void
    {
        $registrar = $container->get(Registrar::class);
        $declarations = $this->declarations;

        \add_action(
            Hooks::INIT,
            static function () use ($registrar, $declarations): void {
                foreach ($declarations as $declaration) {
                    if ($declaration instanceof PostType) {
                        $registrar->registerPostType($declaration);

                        continue;
                    }

                    if ($declaration instanceof Taxonomy) {
                        $registrar->registerTaxonomy($declaration);

                        continue;
                    }

                    $registrar->registerRestRoute($declaration);
                }
            },
            priority: 10,
            accepted_args: 0,
        );
    }
}

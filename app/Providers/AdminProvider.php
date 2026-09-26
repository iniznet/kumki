<?php

declare(strict_types=1);

namespace Iniznet\Kumki\Providers;

use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Contracts\ServiceProvider;

/**
 * The admin seam. Every menu page, metabox, notice and list column this plugin
 * registers is named here and nowhere else.
 *
 * The empty plugin registers none, and that is not a gap: the field package's
 * FieldsUiProvider is registered by FieldsProvider and derives the metaboxes, the
 * save entry, the field route and the settings pages from the declarations in
 * Contracts\Panels and Contracts\OptionScreens. A host that declares no panel and no
 * option screen attaches none of them.
 */
final class AdminProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        // The empty plugin registers no admin screens.
    }

    public function boot(Container $container): void
    {
        // No admin registration at file scope, and none here yet.
    }
}

<?php

declare(strict_types=1);

namespace Iniznet\Kumki\Providers;

use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Contracts\ServiceProvider;

/**
 * The field seam. A feature's schema declares its field groups through the field
 * package's Contracts\FieldRegistry, which FieldsProvider already registered under
 * its contract id, and the option screens through Contracts\OptionScreens, which is
 * what makes the field package attach the settings pages at all.
 *
 * The empty plugin declares no groups. Step one of the six-step recipe happens in a
 * feature module, not here.
 */
final class EditorProvider implements ServiceProvider
{
    public function register(Container $container): void
    {
        // Field groups and option screens are declared by features, one StorageTarget
        // per field.
    }

    public function boot(Container $container): void
    {
        // Nothing attaches yet; the field slices belong to the feature work.
    }
}

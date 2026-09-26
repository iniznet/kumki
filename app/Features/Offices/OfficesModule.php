<?php

/**
 * The offices module.
 *
 * It registers the feature's repository under its own class and resolves the three
 * collaborators it reads by their contract ids — the reader, the field layer and the
 * field query builder — which is what lets the feature hold no package implementation
 * in its constructor at all.
 *
 * A module boots after every provider, so the content model and the field panel are
 * already declared by the time this runs; nothing is attached here and nothing is
 * discovered. The declarations are the registration.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Features\Offices;

use Iniznet\Mahout\Content\PostReader;
use Iniznet\Mahout\Fields\Contracts\FieldQuery;
use Iniznet\Mahout\Fields\Contracts\FieldReader;
use Iniznet\Mahout\Kernel\Container;
use Iniznet\Mahout\Kernel\Contracts\Module;

final class OfficesModule implements Module
{
    public function register(Container $container): void
    {
        $container->set(new OfficesRepository(
            reader: $container->get(PostReader::class),
            fields: $container->get(FieldReader::class),
            query: $container->get(FieldQuery::class),
            mapper: new OfficeMapper(),
        ));
    }

    public function boot(Container $container): void
    {
        // The content model is declared in config/content-types.php and the panel in
        // config/fields.php; both are registered by their providers.
    }
}

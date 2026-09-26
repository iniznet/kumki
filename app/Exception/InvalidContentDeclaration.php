<?php

/**
 * `config/content-types.php` returned something the content package cannot register.
 *
 * The declaration list is the one place the content model is written down, so a
 * wrong shape in it is refused at boot rather than skipped at registration.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Exception;

final class InvalidContentDeclaration extends \LogicException implements PluginException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forType(string $type): self
    {
        return new self(\sprintf('A content declaration is a %s; config/content-types.php returns a list of PostType, Taxonomy or RestRoute.', $type));
    }

    public static function forShape(string $reason): self
    {
        return new self(\sprintf('The content declaration list is unusable: %s.', $reason));
    }
}

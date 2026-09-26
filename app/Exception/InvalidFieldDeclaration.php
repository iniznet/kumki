<?php

/**
 * A field declaration file returned something the field layer cannot register.
 *
 * `config/fields.php` and `config/display-options.php` are the declaration sites, so
 * a wrong shape in either is refused at boot rather than discovered as a missing
 * metabox a week later.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Exception;

final class InvalidFieldDeclaration extends \LogicException implements PluginException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forType(string $type): self
    {
        return new self(\sprintf('A field declaration is a %s; config/fields.php returns a list of FieldPanel.', $type));
    }

    public static function forOptionScreenType(string $type): self
    {
        return new self(\sprintf('An option-screen declaration is a %s; config/display-options.php returns a list of OptionScreen.', $type));
    }
}

<?php

/**
 * `config/entries.php` did not return a list.
 *
 * The entry list is the host's declaration of what the asset pipeline builds, so the
 * host names the failure: the assets package reports a malformed *entry*, and nothing
 * in it can be reached before the file is read.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Exception;

final class InvalidEntryDeclaration extends \LogicException implements PluginException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forType(string $type): self
    {
        return new self(\sprintf('config/entries.php returned %s; it returns a list of entry declarations.', $type));
    }

    public static function forEntry(string $type): self
    {
        return new self(\sprintf('An entry in config/entries.php is a %s; every entry is a map of handle, source and context.', $type));
    }
}

<?php

/**
 * The build's classmap is not the documented shape: an object of names to
 * class-name strings. A corrupt build artifact is a broken build, and the plugin
 * refuses rather than guessing class names.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Exception;

final class ClassMapMalformed extends \RuntimeException implements PluginException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function because(string $reason): self
    {
        return new self(\sprintf('The build classmap is malformed: %s', $reason));
    }

    public static function becauseJson(\JsonException $failure): self
    {
        return new self(\sprintf('The build classmap is not parseable JSON: %s', $failure->getMessage()));
    }
}

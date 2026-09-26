<?php

/**
 * The service graph was resolved before `Bootstrap::run()` booted it.
 *
 * A block render callback registered at file scope, or a hook attached before the
 * composition root ran, reaches this. The condition is named rather than the call
 * site because the fix is always the same: boot first.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Exception;

final class NotBooted extends \LogicException implements PluginException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function beforeResolution(): self
    {
        return new self('The plugin has not booted; Bootstrap::run() registers the graph the first request resolves through.');
    }
}

<?php

/**
 * The marker every exception this plugin throws implements.
 *
 * A consumer catching this name rather than a concrete class is stating that the
 * failure belongs to this host, which is the only promise a marker can keep.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Exception;

interface PluginException extends \Throwable
{
}

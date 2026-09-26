<?php

/**
 * This installation's own paths and URLs, named once from the main plugin file.
 *
 * `plugin_dir_url()` resolves a URL from a *file*, not a directory, and a provider
> that reaches for `dirname(__DIR__, 2)` gets one level right and the other wrong.
 * The composition root has the only correct answer — the file core loaded — so it
 * registers it here and every path or URL in this plugin is derived from that one
 * value.
 *
 * A value object: it holds a string and resolves no collaborator.
 */

declare(strict_types=1);

namespace Iniznet\Kumki\Support;

final readonly class PluginPaths
{
    public function __construct(
        private string $file,
    ) {
    }

    /**
     * The main plugin file, exactly as core names it.
     */
    public function file(): string
    {
        return $this->file;
    }

    /**
     * The plugin's own directory.
     */
    public function directory(): string
    {
        return \dirname($this->file);
    }

    /**
     * One file inside this installation, addressed from its root.
     */
    public function path(string $relative): string
    {
        return $this->directory().'/'.ltrim($relative, '/');
    }

    /**
     * The build directory's URL, without its trailing slash: the base the assets
     * package prefixes manifest entries with.
     */
    public function buildUrl(): string
    {
        return untrailingslashit(\plugin_dir_url($this->file).'build');
    }
}

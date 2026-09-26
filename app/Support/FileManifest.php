<?php

declare(strict_types=1);

namespace Iniznet\Kumki\Support;

use Iniznet\Mahout\Assets\Contracts\ManifestSource;

/**
 * The theme's build manifest source: build/manifest.json.
 *
 * The assets package reads the manifest through this boundary, so the
 * missing-manifest behaviour is the package's, and the theme supplies only
 * the path and the bytes. The package's own file implementation is internal
 * to it, so the theme ships the four lines itself.
 */
final readonly class FileManifest implements ManifestSource
{
    public function __construct(private string $path)
    {
    }

    public function path(): string
    {
        return $this->path;
    }

    public function readable(): bool
    {
        return is_file($this->path);
    }

    public function contents(): string
    {
        $contents = file_get_contents($this->path);

        if (false === $contents) {
            throw \Iniznet\Mahout\Assets\Exception\FileUnreadable::at($this->path);
        }

        return $contents;
    }
}

<?php

declare(strict_types=1);

namespace Iniznet\Kumki\Support;

use Iniznet\Kumki\Exception\ClassMapMalformed;

/**
 * The one class-name resolver markup references.
 *
 * Markup writes class="<?= $c('card') ?>" and the preset decides what the name
 * becomes: the semantic name itself for native and tailwind, a build-emitted
 * hashed name for css-modules. The mapping lives in build/classmap.json, which
 * only the css-modules build emits; a build that has not run is the declared
 * empty state, and the semantic name is what every preset's markup resolves to
 * until that build exists.
 *
 * A value constructor: it holds no state beyond its own map and resolves no
 * collaborator, which is the contract's permitted static shape.
 */
final readonly class ClassResolver
{
    /**
     * @param array<string, string> $map
     */
    private function __construct(private array $map)
    {
    }

    /**
     * @throws ClassMapMalformed when the file exists and is not the documented shape
     */
    public static function fromClassmapFile(string $path): self
    {
        if (!is_file($path)) {
            return new self([]);
        }

        $contents = file_get_contents($path);

        if (false === $contents) {
            throw ClassMapMalformed::because(sprintf('the file at %s could not be read', $path));
        }

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw ClassMapMalformed::becauseJson($exception);
        }

        if (!is_array($decoded)) {
            throw ClassMapMalformed::because('the classmap is not a JSON object');
        }

        $map = [];
        foreach ($decoded as $name => $className) {
            if (!is_string($name) || !is_string($className)) {
                throw ClassMapMalformed::because('every entry must map a name to a class-name string');
            }

            $map[$name] = $className;
        }

        return new self($map);
    }

    public function resolve(string $name): string
    {
        return $this->map[$name] ?? $name;
    }

    public function __invoke(string $name): string
    {
        return $this->resolve($name);
    }
}

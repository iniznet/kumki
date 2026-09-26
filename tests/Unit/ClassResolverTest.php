<?php

declare(strict_types=1);

namespace Iniznet\Kumki\Tests\Unit;

use Iniznet\Kumki\Exception\ClassMapMalformed;
use Iniznet\Kumki\Support\ClassResolver;
use PHPUnit\Framework\TestCase;

/**
 * The class resolver's invariants: a missing classmap is the declared empty
 * state, a malformed one is loud, and resolution falls back to the semantic
 * name the markup wrote.
 */
final class ClassResolverTest extends TestCase
{
    public function testAMissingClassmapIsTheEmptyState(): void
    {
        $resolver = ClassResolver::fromClassmapFile(__DIR__.'/no-such-classmap.json');

        self::assertSame('card', $resolver->resolve('card'));
    }

    public function testAnEmptyClassmapResolvesEveryNameToItself(): void
    {
        $resolver = ClassResolver::fromClassmapFile(dirname(__DIR__).'/fixtures/classmap-empty.json');

        self::assertSame('main', $resolver->resolve('main'));
        self::assertSame('main', $resolver('main'));
    }

    public function testAMappedNameResolvesToItsBuildName(): void
    {
        $map = __DIR__.'/../fixtures/classmap-resolved.json';
        file_put_contents($map, '{"lead": "lead_1a2b3"}');

        try {
            $resolver = ClassResolver::fromClassmapFile($map);

            self::assertSame('lead_1a2b3', $resolver->resolve('lead'));
            self::assertSame('unknown', $resolver->resolve('unknown'));
        } finally {
            unlink($map);
        }
    }

    public function testAMalformedClassmapIsLoud(): void
    {
        $map = __DIR__.'/../fixtures/classmap-malformed.json';
        file_put_contents($map, '{not json');

        try {
            $this->expectException(ClassMapMalformed::class);

            ClassResolver::fromClassmapFile($map);
        } finally {
            unlink($map);
        }
    }

    public function testAClassmapThatIsNotAnObjectIsMalformed(): void
    {
        $map = __DIR__.'/../fixtures/classmap-list.json';
        file_put_contents($map, '["lead"]');

        try {
            $this->expectException(ClassMapMalformed::class);

            ClassResolver::fromClassmapFile($map);
        } finally {
            unlink($map);
        }
    }

    public function testAClassmapWithNonStringEntriesIsMalformed(): void
    {
        $map = __DIR__.'/../fixtures/classmap-nonstring.json';
        file_put_contents($map, '{"lead": 42}');

        try {
            $this->expectException(ClassMapMalformed::class);

            ClassResolver::fromClassmapFile($map);
        } finally {
            unlink($map);
        }
    }
}

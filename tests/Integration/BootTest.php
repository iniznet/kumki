<?php

declare(strict_types=1);

namespace Iniznet\Kumki\Tests\Integration;

use Iniznet\Kumki\Bootstrap;

/**
 * The composition root boots the declared graph exactly once per suite, from the
 * same entry point production uses, and it owns the process while doing it.
 *
 * The ownership assertion is the one a plugin has and a theme starter does not:
 * because a plugin loads before the theme, this host is the one that wins the claim
 * on a site that installed the packages twice, and the assertion is what keeps that
 * ordering visible rather than incidental (mahout-kernel ADR-0007).
 */
final class BootTest extends \WP_UnitTestCase
{
    /** @return list<class-string> */
    private static function declaredServices(): array
    {
        return [
            \Iniznet\Kumki\Support\PluginPaths::class,
            \Iniznet\Mahout\Assets\AssetsConfig::class,
            \Iniznet\Mahout\Assets\EntryEnqueuer::class,
            \Iniznet\Mahout\Content\Contracts\Registrar::class,
            \Iniznet\Mahout\Db\Contracts\SqlConnection::class,
            \Iniznet\Mahout\Db\Contracts\TableGateway::class,
            \Iniznet\Mahout\Db\MigrationRunner::class,
            \Iniznet\Mahout\Fields\Contracts\FieldReader::class,
            \Iniznet\Mahout\Fields\Contracts\FieldRegistry::class,
            \Iniznet\Mahout\Fields\Contracts\FieldWriter::class,
            \Iniznet\Kumki\Support\ClassResolver::class,
        ];
    }

    public function testTheCompositionRootDeclaredTheGraph(): void
    {
        $services = Bootstrap::services();

        foreach (self::declaredServices() as $id) {
            self::assertTrue($services->has($id), sprintf('The composition root did not declare %s.', $id));
        }
    }

    public function testThePluginIsTheRootOfRecordForThisProcess(): void
    {
        // The entry point claims the process; a second host building its own kernel
        // here is refused rather than quietly served this one's classes.
        $this->expectException(\Iniznet\Mahout\Kernel\Exception\SecondCompositionRoot::class);

        \Iniznet\Mahout\Kernel\Kernel::inWordPress(self::class);
    }

    public function testTheDeclaredPathsPointAtThisInstallation(): void {
        $paths = Bootstrap::services()->get(\Iniznet\Kumki\Support\PluginPaths::class);

        self::assertSame('kumki.php', basename($paths->file()), 'the main file is the one core loaded, whatever the separator of the machine that loaded it.');
        self::assertFileExists($paths->path('config/content-types.php'));
    }
}

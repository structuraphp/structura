<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Concerns\Console;

use Composer\InstalledVersions;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Concerns\Console\Version;

#[CoversMethod(Version::class, 'getInfos')]
#[CoversMethod(Version::class, 'getProjectVersion')]
final class VersionTest extends TestCase
{
    use Version;

    public function testInfos(): void
    {
        self::assertSame(
            <<<'INFO'
            Version:       Structura project.version
            Runtime:       PHP php.version
            Configuration: undefined
            INFO,
            $this->getInfos(
                projectVersion: 'project.version',
                phpVersion: 'php.version',
            ),
        );
    }

    public function testInfosUseInstalledVersionOutsidePhar(): void
    {
        self::assertStringStartsWith(
            'Version:       Structura ' . (InstalledVersions::getVersion('structuraphp/structura') ?? 'UNKNOWN') . PHP_EOL,
            $this->getInfos(),
        );
    }

    public function testProjectVersionUsesGitTagInPharBuilds(): void
    {
        self::assertSame('0.10.0-rc.1', $this->getProjectVersion('0.10.0-rc.1'));
    }
}

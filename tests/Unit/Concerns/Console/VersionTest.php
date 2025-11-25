<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Concerns\Console;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Concerns\Console\Version;

#[CoversClass(Version::class)]
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
}

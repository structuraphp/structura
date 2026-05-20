<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveFilePermission;
use StructuraPhp\Structura\Concerns\ExprScript\ThirdPartyAssert;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(ToHaveFilePermission::class)]
#[CoversMethod(ThirdPartyAssert::class, 'toHaveFilePermission')]
final class ToHaveFilePermissionTest extends TestCase
{
    use ArchitectureAsserts;

    private string $testDir;

    protected function setUp(): void
    {
        $this->testDir = \sys_get_temp_dir() . '/structura_test_' . uniqid();
        @mkdir($this->testDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $filesystem = new Filesystem();
        if (\is_dir($this->testDir)) {
            $filesystem->remove($this->testDir);
        }
    }

    public function testToHaveFilePermissionWithCorrectPermission(): void
    {
        $filePath = $this->testDir . '/test.php';
        file_put_contents($filePath, '<?php echo "test";');
        chmod($filePath, 0644);

        $rules = $this
            ->allScripts()
            ->fromRaw('<?php echo "test";', $filePath)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toHaveFilePermission('0644'),
            );

        self::assertRulesPass(
            $rules,
            'to have file permission <promote>0644</promote>',
        );
    }

    public function testToHaveFilePermissionWithWrongPermission(): void
    {
        $filePath = $this->testDir . '/test.php';
        file_put_contents($filePath, '<?php echo "test";');
        chmod($filePath, 0644);

        $rules = $this
            ->allScripts()
            ->fromRaw('<?php echo "test";', $filePath)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toHaveFilePermission('0755'),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have file permission <promote>0755</promote> but is <fire>0644</fire>',
                $filePath,
            ),
            0,
        );
    }
}

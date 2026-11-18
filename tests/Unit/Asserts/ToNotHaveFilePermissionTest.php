<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotHaveFilePermission;
use StructuraPhp\Structura\Concerns\ExprScript\ThirdPartyAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use StructuraPhp\Structura\Tests\Helper\Sandbox;

#[CoversClass(ToNotHaveFilePermission::class)]
#[CoversMethod(ThirdPartyAssert::class, 'toNotHaveFilePermission')]
final class ToNotHaveFilePermissionTest extends TestCase
{
    use ArchitectureAsserts;
    use Sandbox;

    protected function setUp(): void
    {
        $this->enterSandbox();
    }

    protected function tearDown(): void
    {
        $this->leaveSandbox();
    }

    public function testToNotHaveFilePermissionWithOtherPermission(): void
    {
        $filePath = $this->createFile(0644);

        $rules = $this
            ->allScripts()
            ->fromRaw('<?php echo "test";', $filePath)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotHaveFilePermission('0777'),
            );

        self::assertRulesPass(
            $rules,
            'to not have file permission <promote>0777</promote>',
        );
    }

    public function testToNotHaveFilePermissionWithMissingFile(): void
    {
        $rules = $this
            ->allScripts()
            ->fromRaw('<?php echo "test";')
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotHaveFilePermission('0777'),
            );

        self::assertRulesPass(
            $rules,
            'to not have file permission <promote>0777</promote>',
        );
    }

    public function testShouldFailToNotHaveFilePermissionWithScript(): void
    {
        $filePath = $this->createFile(0777);

        $rules = $this
            ->allScripts()
            ->fromRaw('<?php echo "test";', $filePath)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toNotHaveFilePermission('0777'),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not have file permission <fire>0777</fire>',
                $filePath,
            ),
        );
    }

    public function testShouldFailToNotHaveFilePermissionWithClass(): void
    {
        $filePath = $this->createFile(0777);

        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}', $filePath)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveFilePermission('0777'),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not have file permission <fire>0777</fire>',
                $filePath,
            ),
        );
    }

    private function createFile(int $permission): string
    {
        $filePath = $this->sandbox . '/test.php';
        file_put_contents($filePath, '<?php echo "test";');
        chmod($filePath, $permission);

        return $filePath;
    }
}

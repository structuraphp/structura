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
use StructuraPhp\Structura\Tests\Helper\Sandbox;

#[CoversClass(ToHaveFilePermission::class)]
#[CoversMethod(ThirdPartyAssert::class, 'toHaveFilePermission')]
final class ToHaveFilePermissionTest extends TestCase
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

    public function testToHaveFilePermissionWithCorrectPermission(): void
    {
        $filePath = $this->sandbox . '/test.php';
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
        $filePath = $this->sandbox . '/test.php';
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
        );
    }
}

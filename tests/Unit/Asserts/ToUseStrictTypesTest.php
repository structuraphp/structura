<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToUseDeclare;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToUseDeclare::class)]
#[CoversMethod(Expr::class, 'toUseStrictTypes')]
final class ToUseStrictTypesTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToUserStrictType(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php declare(strict_types=1); class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toUseStrictTypes(),
            );

        self::assertRulesPass(
            $rules,
            'to use declare <promote>strict_types=1</promote>',
        );

        $rules = $this
            ->allScripts()
            ->fromRaw('<?php declare(strict_types=1); $foo=1;')
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toUseStrictTypes(),
            );

        self::assertRulesPass(
            $rules,
            'to use declare <promote>strict_types=1</promote>',
        );
    }

    public function testShouldFailToUserStrictType(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toUseStrictTypes(),
            );

        self::assertRulesViolation(
            $rules,
            'Resource <promote>Foo</promote> must use declaration <promote>strict_types=1</promote>',
        );
    }

    #[DataProvider('getScriptTypeProvider')]
    public function testShouldFailToUserStrictTypeWithScript(
        string $raw,
        string $exceptName,
    ): void {
        $rules = $this
            ->allScripts()
            ->fromRaw($raw)
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->toUseStrictTypes(),
            );

        self::assertRulesViolation(
            $rules,
            sprintf(
                'Resource <promote>%s</promote> must use declaration <promote>strict_types=1</promote>',
                $exceptName,
            ),
        );
    }

    public static function getScriptTypeProvider(): Generator
    {
        yield 'script with namespace' => [
            '<?php namespace Foo; $foo = 1;',
            'Foo',
        ];

        yield 'script without namespace' => [
            '<?php $foo = 1;',
            'tmp/run_0.php',
        ];
    }
}

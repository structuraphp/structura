<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToExtendNothing;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToExtendNothing::class)]
#[CoversMethod(Expr::class, 'toExtendsNothing')]
final class ToExtendNothingTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeExtendsNothing')]
    public function testToExtendsNothing(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toExtendsNothing(),
            );

        self::assertRulesPass($rules, 'to extend nothing');
    }

    public static function getClassLikeExtendsNothing(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};'];

        yield 'class' => ['<?php class Foo {}'];
    }

    #[DataProvider('getClassLikeExtends')]
    public function testShouldFailToExtendsNothing(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toExtendsNothing(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must extend nothing but extends <fire>%s</fire>',
                $exceptName,
                'Exception',
            ),
        );
    }

    #[DataProvider('getClassLikeExtends')]
    public function testExceptToExtendsNothing(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->except(
                $exceptName,
                static fn (Except $assert): Except => $assert->toExtendsNothing(),
            )
            ->should(
                static fn (Expr $assert): Expr => $assert->toExtendsNothing(),
            );

        self::assertRulesPass($rules, 'to extend nothing');
    }

    public static function getClassLikeExtends(): Generator
    {
        yield 'anonymous class' => ['<?php new class extends \Exception {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo extends \Exception {}'];
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToImplementNothingTest::class)]
#[CoversMethod(Expr::class, 'toImplementNothing')]
final class ToImplementNothingTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeImplementsNothing')]
    public function testToImplementsNothing(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toImplementNothing(),
            );

        self::assertRulesPass($rules);
    }

    #[DataProvider('getClassLikeImplements')]
    public function testShouldFailToImplementsNothing(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toImplementNothing(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not implement anything',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeImplementsNothing(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];
    }

    public static function getClassLikeImplements(): Generator
    {
        yield 'anonymous class' => ['<?php new class implements BarInterface {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo implements BarInterface {}'];

        yield 'enum' => ['<?php enum Foo implements BarInterface {}'];
    }
}

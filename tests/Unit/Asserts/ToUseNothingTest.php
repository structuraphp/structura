<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;
use Structura\ValueObjects\ExpectValueObject;

#[CoversClass(ToUseNothingTest::class)]
#[CoversMethod(Expr::class, 'toUseNothing')]
final class ToUseNothingTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutTrait')]
    public function testToUseNothing(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toUseNothing(),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithTrait')]
    public function testToUseNothingWithExpect(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->that(static fn(Expr $expr): Expr => $expr->toBeClasses())
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toUseNothing(new ExpectValueObject(['Foo'])),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithTrait')]
    public function testShouldFailToUseNothing(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>%s</promote> must not use a trait',
                $exceptName,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toUseNothing(),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class { use \HasFactory; };', 'Anonymous'];
        yield 'class' => ['<?php class Foo { use \HasFactory; }'];
        yield 'enum' => ['<?php enum Foo { use \HasFactory; };'];
        yield 'interface' => ['<?php interface Foo { use \HasFactory; }'];
    }

    public static function getClassLikeWithoutTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};'];
        yield 'class' => ['<?php class Foo {}'];
        yield 'enum' => ['<?php enum Foo {};'];
        yield 'interface' => ['<?php interface Foo {}'];
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotUseTrait;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotUseTrait::class)]
#[CoversMethod(Expr::class, 'toNotUseTrait')]
final class ToNotUseTraitTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutTrait')]
    public function testToUseNothing(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotUseTrait(),
            );

        self::assertRules($rules);
    }

    /**
     * @param class-string $exceptName
     */
    #[DataProvider('getClassLikeWithTrait')]
    public function testToUseNothingWithExpect(string $raw, string $exceptName): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->that(static fn (Expr $expr): Expr => $expr->toBeClasses())
            ->except(
                static fn (Except $except): Except => $except
                    ->byRule($exceptName, ToNotUseTrait::class),
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotUseTrait(),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithTrait')]
    public function testShouldFailToUseNothing(string $raw, string $exceptName): void
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
                static fn (Expr $assert): Expr => $assert
                    ->toNotUseTrait(),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class { use \HasFactory; };', 'Anonymous'];

        yield 'class' => ['<?php class Foo { use \HasFactory; }', 'Foo'];

        yield 'enum' => ['<?php enum Foo { use \HasFactory; };', 'Foo'];

        yield 'interface' => ['<?php interface Foo { use \HasFactory; }', 'Foo'];
    }

    public static function getClassLikeWithoutTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];

        yield 'interface' => ['<?php interface Foo {}'];
    }
}

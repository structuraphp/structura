<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveAttribute;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveAttribute::class)]
#[CoversMethod(Expr::class, 'toHaveAttribute')]
final class ToHaveAttributeTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithAttribute')]
    public function testToHaveAttribute(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveAttribute('Attribute'),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeWithoutAttribute')]
    public function testShouldFailToHaveAttribute(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>%s</promote> must have attribute <promote>Attribute</promote>',
                $exceptName,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveAttribute('Attribute'),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeWithAttribute(): Generator
    {
        yield 'class' => ['<?php #[Attribute] class Foo {}'];

        yield 'enum' => ['<?php #[Attribute] enum Foo {}'];

        yield 'interface' => ['<?php #[Attribute] interface Foo {}'];

        yield 'trait' => ['<?php #[Attribute] trait Foo {}'];
    }

    public static function getClassLikeWithoutAttribute(): Generator
    {
        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }
}

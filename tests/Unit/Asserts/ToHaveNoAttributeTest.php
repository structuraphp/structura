<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveNoAttribute;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveNoAttribute::class)]
#[CoversMethod(Expr::class, 'toHaveNoAttribute')]
final class ToHaveNoAttributeTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutAttribute')]
    public function testToHaveAttribute(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveNoAttribute(),
            );

        self::assertRulesPass($rules, 'to have no attribute');
    }

    public static function getClassLikeWithoutAttribute(): Generator
    {
        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeWithAttribute')]
    public function testShouldFailToHaveAttribute(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveNoAttribute(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not have attribute',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeWithAttribute(): Generator
    {
        yield 'class' => ['<?php #[Attribute] class Foo {}'];

        yield 'enum' => ['<?php #[Attribute] enum Foo {}'];

        yield 'interface' => ['<?php #[Attribute] interface Foo {}'];

        yield 'trait' => ['<?php #[Attribute] trait Foo {}'];
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Attribute;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveOnlyAttribute;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToHaveOnlyAttribute::class)]
#[CoversMethod(Expr::class, 'toHaveNoAttribute')]
final class ToHaveOnlyAttributeTest extends TestCase
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
                    ->toHaveOnlyAttribute(Attribute::class),
            );

        self::assertRulesPass(
            $rules,
            'to have only attribute <promote>Attribute</promote>',
        );
    }

    public static function getClassLikeWithAttribute(): Generator
    {
        yield 'class' => ['<?php #[Attribute] class Foo {}'];

        yield 'enum' => ['<?php #[Attribute] enum Foo {}'];

        yield 'interface' => ['<?php #[Attribute] interface Foo {}'];

        yield 'trait' => ['<?php #[Attribute] trait Foo {}'];
    }

    #[DataProvider('getClassLikeWithoutAttribute')]
    public function testShouldFailToHaveAttribute(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveOnlyAttribute(Attribute::class),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must have only attribute <promote>%s</promote>',
                $exceptName,
                Attribute::class,
            ),
        );
    }

    public static function getClassLikeWithoutAttribute(): Generator
    {
        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }
}

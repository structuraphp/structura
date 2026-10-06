<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Deprecated;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotHaveAttribute;
use StructuraPhp\Structura\Concerns\Expr\RelationAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotHaveAttribute::class)]
#[CoversMethod(RelationAssert::class, 'toNotHaveAttribute')]
final class ToNotHaveAttributeTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeWithoutAttribute')]
    public function testToNotHaveAttribute(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveAttribute(Deprecated::class),
            );

        self::assertRulesPass(
            $rules,
            'to not have attribute <promote>Deprecated</promote>',
        );
    }

    public static function getClassLikeWithoutAttribute(): Generator
    {
        yield 'anonymous class' => ['<?php return new #[\Foo] class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class with another attribute' => ['<?php #[\Foo] class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeWithAttribute')]
    public function testShouldFailToNotHaveAttribute(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveAttribute(Deprecated::class),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not have attribute <fire>Deprecated</fire>',
                $exceptName,
            ),
            2,
        );
    }

    public static function getClassLikeWithAttribute(): Generator
    {
        yield 'anonymous class' => ["<?php return new\n#[\\Deprecated] class {};", 'Anonymous'];

        yield 'class' => ["<?php\n#[\\Foo, \\Deprecated] class Foo {}"];

        yield 'enum' => ["<?php\n#[\\Deprecated] enum Foo {}"];

        yield 'interface' => ["<?php\n#[\\Deprecated] interface Foo {}"];

        yield 'trait' => ["<?php\n#[\\Deprecated] trait Foo {}"];
    }

    public function testShouldFailToNotHaveAttributeRepeated(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw("<?php\n#[\\Deprecated]\n#[\\Deprecated]\nclass Foo {}")
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotHaveAttribute(Deprecated::class),
            );

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>Foo</promote> must not have attribute <fire>Deprecated</fire>',
                'Resource <promote>Foo</promote> must not have attribute <fire>Deprecated</fire>',
            ],
            [2, 3],
        );
    }
}

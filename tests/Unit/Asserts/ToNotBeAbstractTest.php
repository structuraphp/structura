<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotBeAbstract;
use StructuraPhp\Structura\Concerns\Expr\TypeAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotBeAbstract::class)]
#[CoversMethod(TypeAssert::class, 'toNotBeAbstract')]
final class ToNotBeAbstractTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeNonAbstract')]
    public function testToNotBeAbstract(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toNotBeAbstract(),
            );

        self::assertRulesPass($rules, 'to not be abstract');
    }

    public static function getClassLikeNonAbstract(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'final class' => ['<?php final class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    public function testShouldFailToNotBeAbstract(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php abstract class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert->toNotBeAbstract(),
            );

        self::assertRulesViolation(
            $rules,
            'Resource <promote>Foo</promote> must not be an abstract class',
        );
    }
}

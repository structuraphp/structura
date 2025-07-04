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

#[CoversClass(ToBeAbstractTest::class)]
#[CoversMethod(Expr::class, 'toBeAbstract')]
final class ToBeAbstractTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeAbstract(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php abstract class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeAbstract(),
            );

        self::assertRulesPass($rules, 'to be abstract');
    }

    #[DataProvider('getClassLikeNonAbstract')]
    public function testShouldFailToBeAbstract(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeAbstract(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> be an abstract class',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeNonAbstract(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }
}

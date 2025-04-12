<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeAbstractTest::class)]
class ToBeAbstractTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeAbstract(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php abstract class Foo {}')
            ->should(
                static fn(Expr $assert): Expr => $assert->toBeAbstract(),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeNonAbstract')]
    public function testShouldFailToBeAbstract(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>%s</promote> be an abstract class',
                $exceptName,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert->toBeAbstract(),
            );

        self::assertRules($rules);
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

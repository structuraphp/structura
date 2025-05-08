<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeAnonymousClasses;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeAnonymousClasses::class)]
#[CoversMethod(Expr::class, 'toBeAnonymousClasses')]
final class ToBeAnonymousClassesTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeAnonymousClasses(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php new class {};')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toBeAnonymousClasses(),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeNonAnonymousClasses')]
    public function testShouldFailToBeAnonymousClasses(string $raw): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            'Resource <promote>Foo</promote> must be an anonymous class',
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toBeAnonymousClasses(),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeNonAnonymousClasses(): Generator
    {
        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }
}

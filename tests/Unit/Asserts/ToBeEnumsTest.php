<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Asserts\ToBeEnums;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeEnums::class)]
#[CoversMethod(Expr::class, 'toBeEnums')]
final class ToBeEnumsTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeEnums(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php enum Foo {}')
            ->should(
                static fn(Expr $assert): Expr => $assert->toBeEnums(),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeNonEnums')]
    public function testShouldFailToBeEnums(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf('Resource <promote>%s</promote> must be an enum', $exceptName),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert->toBeEnums(),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeNonEnums(): Generator
    {
        yield 'class' => ['<?php class Foo {}'];
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];
        yield 'interface' => ['<?php interface Foo {}'];
        yield 'trait' => ['<?php trait Foo {}'];
    }
}

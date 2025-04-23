<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Asserts\ToBeInterfaces;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeInterfaces::class)]
#[CoversMethod(Expr::class, 'toBeInterfaces')]
final class ToBeInterfaceTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeInterface(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php interface Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeInterfaces(),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeNonEnums')]
    public function testShouldFailToBeInterface(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf('Resource <promote>%s</promote> must be an interface', $exceptName),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeInterfaces(),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeNonEnums(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }
}

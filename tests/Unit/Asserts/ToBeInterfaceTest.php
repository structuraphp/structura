<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeInterfaces;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

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

        self::assertRulesPass($rules);
    }

    #[DataProvider('getClassLikeNonEnums')]
    public function testShouldFailToBeInterface(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeInterfaces(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf('Resource <promote>%s</promote> must be an interface', $exceptName),
        );
    }

    public static function getClassLikeNonEnums(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }
}

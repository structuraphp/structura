<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeTraits;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeTraits::class)]
#[CoversMethod(Expr::class, 'toBeTraits')]
final class ToBeTraitsTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeTraits(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php trait Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeTraits(),
            );

        self::assertRulesPass($rules, 'to be traits');
    }

    #[DataProvider('getClassLikeNonTrait')]
    public function testShouldFailToBeTrait(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeTraits(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf('Resource <promote>%s</promote> must be a trait', $exceptName),
        );
    }

    public static function getClassLikeNonTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];
    }
}

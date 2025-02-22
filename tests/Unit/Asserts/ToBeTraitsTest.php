<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Testing\ArchitectureAsserts;

class ToBeTraitsTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeTraits(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php trait Foo {}')
            ->should(
                static fn(Expr $assert): Expr => $assert->toBeTraits(),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeNonTrait')]
    public function testShouldFailToBeTrait(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf('Resource <promote>%s</promote> must be a trait', $exceptName),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert->toBeTraits(),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeNonTrait(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];
        yield 'class' => ['<?php class Foo {}'];
        yield 'enum' => ['<?php enum Foo {}'];
        yield 'interface' => ['<?php interface Foo {}'];
    }
}

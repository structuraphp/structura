<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

class ToBeReadonlyTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeReadonly(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php readonly class Foo {}')
            ->should(
                static fn(Expr $assert): Expr => $assert->toBeReadonly(),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeNonReadonly')]
    public function testShouldFailToBeReadonly(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>%s</promote> must be a read-only class',
                $exceptName,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert->toBeReadonly(),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeNonReadonly(): Generator
    {
        yield 'abstract class' => ['<?php abstract class Foo {}'];
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];
        yield 'class' => ['<?php class Foo {}'];
        yield 'enum' => ['<?php enum Foo {}'];
        yield 'interface' => ['<?php interface Foo {}'];
        yield 'trait' => ['<?php trait Foo {}'];
    }
}

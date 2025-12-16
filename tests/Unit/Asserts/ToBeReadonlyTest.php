<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeReadonly;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeReadonly::class)]
#[CoversMethod(Expr::class, 'toBeReadonly')]
final class ToBeReadonlyTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeReadonly(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php readonly class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeReadonly(),
            );

        self::assertRulesPass($rules, 'to be readonly');
    }

    #[DataProvider('getClassLikeNonReadonly')]
    public function testShouldFailToBeReadonly(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeReadonly(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must be a read-only class',
                $exceptName,
            ),
        );
    }

    #[DataProvider('getClassLikeNonReadonly')]
    public function testExceptToBeReadonly(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->except(
                $exceptName,
                static fn (Except $assert): Except => $assert->toBeReadonly(),
            )
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeReadonly(),
            );

        self::assertRulesPass($rules, 'to be readonly');
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

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotBeFinal;
use StructuraPhp\Structura\Concerns\Expr\TypeAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotBeFinal::class)]
#[CoversMethod(TypeAssert::class, 'toNotBeFinal')]
final class ToNotBeFinalTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeNonFinal')]
    public function testToNotBeFinal(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toNotBeFinal(),
            );

        self::assertRulesPass($rules, 'to not be final');
    }

    public static function getClassLikeNonFinal(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'abstract class' => ['<?php abstract class Foo {}'];

        yield 'enum' => ['<?php enum Foo {};'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    public function testShouldFailToNotBeFinal(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php final class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert->toNotBeFinal(),
            );

        self::assertRulesViolation(
            $rules,
            'Resource <promote>Foo</promote> must not be a final class',
        );
    }
}

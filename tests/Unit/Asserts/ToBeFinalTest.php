<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToBeFinal;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToBeFinal::class)]
#[CoversMethod(Expr::class, 'toBeFinal')]
final class ToBeFinalTest extends TestCase
{
    use ArchitectureAsserts;

    public function testToBeFinal(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php final class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeFinal(),
            );

        self::assertRulesPass($rules, 'to be final');
    }

    #[DataProvider('getClassLikeNonFinal')]
    public function testShouldFailToBeFinal(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeFinal(),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf('Resource <promote>%s</promote> must be a final class', $exceptName),
        );
    }

    public static function getClassLikeNonFinal(): Generator
    {
        yield 'abstract class' => ['<?php abstract class Foo {}'];

        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'interface' => ['<?php interface Foo {}'];

        yield 'readonly class' => ['<?php readonly class Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }
}

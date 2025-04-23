<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Asserts\ToBeFinal;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

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

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeNonFinal')]
    public function testShouldFailToBeFinal(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf('Resource <promote>%s</promote> must be a final class', $exceptName),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert->toBeFinal(),
            );

        self::assertRules($rules);
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

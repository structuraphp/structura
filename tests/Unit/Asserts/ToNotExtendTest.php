<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use ArrayIterator;
use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToNotExtend;
use StructuraPhp\Structura\Concerns\Expr\RelationAssert;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToNotExtend::class)]
#[CoversMethod(RelationAssert::class, 'toNotExtend')]
final class ToNotExtendTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeNotExtendingException')]
    public function testToNotExtend(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotExtend(Exception::class),
            );

        self::assertRulesPass(
            $rules,
            'to not extend <promote>Exception</promote>',
        );
    }

    public static function getClassLikeNotExtendingException(): Generator
    {
        yield 'anonymous class' => ['<?php return new class {};'];

        yield 'class' => ['<?php class Foo {}'];

        yield 'class extending another class' => ['<?php class Foo extends \ArrayIterator {}'];

        yield 'interface' => ['<?php interface Foo extends \Countable {}'];

        yield 'enum' => ['<?php enum Foo {}'];

        yield 'trait' => ['<?php trait Foo {}'];
    }

    #[DataProvider('getClassLikeExtendingException')]
    public function testShouldFailToNotExtend(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotExtend(Exception::class),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must not extend <fire>Exception</fire>',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeExtendingException(): Generator
    {
        yield 'anonymous class' => ['<?php return new class extends \Exception {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo extends \Exception {}'];
    }

    public function testShouldFailToNotExtendWithMultipleViolations(): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw('<?php interface Foo extends \Exception, \Countable, \ArrayIterator {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toNotExtend([Exception::class, ArrayIterator::class]),
            );

        self::assertRulesViolation(
            $rules,
            [
                'Resource <promote>Foo</promote> must not extend <fire>Exception</fire>',
                'Resource <promote>Foo</promote> must not extend <fire>ArrayIterator</fire>',
            ],
            [1, 1],
        );
    }
}

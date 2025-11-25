<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToExtend;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversClass(ToExtend::class)]
#[CoversMethod(Expr::class, 'toExtend')]
final class ToExtendTest extends TestCase
{
    use ArchitectureAsserts;

    #[DataProvider('getClassLikeExtends')]
    public function testToExtend(string $raw): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toExtend(Exception::class),
            );

        self::assertRulesPass(
            $rules,
            'to extend <promote>Exception</promote>',
        );
    }

    public static function getClassLikeExtends(): Generator
    {
        yield 'anonymous class' => ['<?php new class extends \Exception {};'];

        yield 'class' => ['<?php class Foo extends \Exception {}'];
    }

    #[DataProvider('getClassLikeExtendsNothing')]
    public function testShouldFailToExtends(string $raw, string $exceptName = 'Foo'): void
    {
        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toExtend(Exception::class),
            );

        self::assertRulesViolation(
            $rules,
            \sprintf(
                'Resource <promote>%s</promote> must extend by <promote>Exception</promote>',
                $exceptName,
            ),
        );
    }

    public static function getClassLikeExtendsNothing(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];

        yield 'class' => ['<?php class Foo {}'];
    }
}

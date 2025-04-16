<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Asserts;

use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Structura\Asserts\ToExtend;
use Structura\Expr;
use Structura\Tests\Helper\ArchitectureAsserts;

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
                static fn(Expr $assert): Expr => $assert
                    ->toExtend(Exception::class),
            );

        self::assertRules($rules);
    }

    #[DataProvider('getClassLikeExtendsNothing')]
    public function testShouldFailToExtends(string $raw, string $exceptName = 'Foo'): void
    {
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage(
            \sprintf(
                'Resource <promote>%s</promote> must extend by <promote>Exception</promote>',
                $exceptName,
            ),
        );

        $rules = $this
            ->allClasses()
            ->fromRaw($raw)
            ->should(
                static fn(Expr $assert): Expr => $assert
                    ->toExtend(Exception::class),
            );

        self::assertRules($rules);
    }

    public static function getClassLikeExtendsNothing(): Generator
    {
        yield 'anonymous class' => ['<?php new class {};', 'Anonymous'];
        yield 'class' => ['<?php class Foo {}'];
    }

    public static function getClassLikeExtends(): Generator
    {
        yield 'anonymous class' => ['<?php new class extends \Exception {};'];
        yield 'class' => ['<?php class Foo extends \Exception {}'];
    }
}

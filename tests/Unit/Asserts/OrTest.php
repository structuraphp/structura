<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use AppendIterator;
use ArrayIterator;
use Exception;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;

#[CoversMethod(Expr::class, 'or')]
final class OrTest extends TestCase
{
    use ArchitectureAsserts;

    /**
     * @param array<int,string> $raws
     */
    #[DataProvider('getClassLikeProvider')]
    public function testShouldOr(array $raws): void
    {
        $rules = $this
            ->allClasses()
            ->fromRawMultiple($raws)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->or(
                        static fn (Expr $assertion): Expr => $assertion
                            ->toExtend(InvalidArgumentException::class)
                            ->toExtend(Exception::class),
                    ),
            );

        self::assertRulesPass(
            $rules,
            <<<TXT
            to extend <promote>InvalidArgumentException</promote>
               | to extend <promote>Exception</promote>
            TXT
        );
    }

    /**
     * @param array<int,string> $raws
     */
    #[DataProvider('getClassLikeProvider')]
    public function testShouldFailToOr(array $raws): void
    {
        $rules = $this
            ->allClasses()
            ->fromRawMultiple($raws)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->or(
                        static fn (Expr $assertion): Expr => $assertion
                            ->toExtend(ArrayIterator::class)
                            ->toExtend(AppendIterator::class),
                    ),
            );

        self::assertRulesViolation(
            $rules,
            'Resource <promote>Foo</promote> must extend by <promote>ArrayIterator</promote>, '
            . 'Resource <promote>Foo</promote> must extend by <promote>AppendIterator</promote>, '
            . 'Resource <promote>Bar</promote> must extend by <promote>ArrayIterator</promote>, '
            . 'Resource <promote>Bar</promote> must extend by <promote>AppendIterator</promote>',
        );
    }

    public static function getClassLikeProvider(): Generator
    {
        yield [
            [
                <<<PHP
                <?php
                class Foo extends \\InvalidArgumentException {}
                PHP,
                <<<PHP
                <?php
                class Bar extends \\Exception {}
                PHP,
            ],
        ];
    }
}

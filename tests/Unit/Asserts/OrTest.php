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
use StructuraPhp\Structura\Tests\Fixture\Exceptions\UserException;
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
            sprintf(
                'Resource <promote>%s</promote> must extend by <promote>ArrayIterator</promote>, '
                . 'Resource <promote>%s</promote> must extend by <promote>AppendIterator</promote>',
                UserException::class,
                UserException::class,
            ),
        );
    }

    public static function getClassLikeProvider(): Generator
    {
        yield [
            [
                <<<PHP
                <?php
                
                declare(strict_types=1);
                
                namespace StructuraPhp\\Structura\\Tests\\Fixture\\Exceptions;
                
                use InvalidArgumentException;
                
                class InvalidException extends InvalidArgumentException {}
                PHP,
                <<<PHP
                <?php
                
                declare(strict_types=1);
                
                namespace StructuraPhp\\Structura\\Tests\\Fixture\\Exceptions;
                
                use Exception;
                
                class UserException extends Exception {}
                PHP,
            ],
        ];
    }
}

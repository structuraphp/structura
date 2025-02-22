<?php

declare(strict_types=1);

namespace Structura\Tests\Feature;

use BadMethodCallException;
use DomainException;
use Exception;
use InvalidArgumentException;
use Structura\Attributes\TestDox;
use Structura\Expr;
use Structura\Testing\TestBuilder;

class TestException extends TestBuilder
{
    #[TestDox('Exceptions architecture rules')]
    public function testExceptionsArchitectureRules(): void
    {
        $this
            ->allClasses()
            ->fromDir('tests/Fixture/Exceptions')
            ->should(
                static fn(Expr $expr): Expr => $expr
                    ->or(
                        static fn(Expr $expr): Expr => $expr
                            ->toExtend(InvalidArgumentException::class)
                            ->toExtend(Exception::class)
                            ->and(
                                static fn(Expr $expr): Expr => $expr
                                    ->toExtend(DomainException::class)
                                    ->toExtend(BadMethodCallException::class),
                            ),
                    ),
            );
    }
}

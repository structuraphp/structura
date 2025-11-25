<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Feature;

use BadMethodCallException;
use DomainException;
use Exception;
use InvalidArgumentException;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;

final class TestException extends TestBuilder
{
    #[TestDox('Exceptions architecture rules')]
    public function testExceptionsArchitectureRules(): void
    {
        $this
            ->allClasses()
            ->fromRawMultiple($this->getClassLikeProvider())
            ->should(
                static fn (Expr $expr): Expr => $expr
                    ->or(
                        static fn (Expr $expr): Expr => $expr
                            ->toExtend(InvalidArgumentException::class)
                            ->toExtend(Exception::class)
                            ->and(
                                static fn (Expr $expr): Expr => $expr
                                    ->toExtend(DomainException::class)
                                    ->toExtend(BadMethodCallException::class),
                            ),
                    ),
            );
    }

    /**
     * @return array<int,string>
     */
    private function getClassLikeProvider(): array
    {
        return [
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
        ];
    }
}

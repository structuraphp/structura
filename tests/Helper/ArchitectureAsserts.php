<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Helper;

use PHPUnit\Framework\Assert;
use StructuraPhp\Structura\Builder\AllClasses;
use StructuraPhp\Structura\Builder\AssertBuilder;
use StructuraPhp\Structura\Builder\RuleBuilder;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Services\ExecuteService;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait ArchitectureAsserts
{
    /**
     * @return AllClasses<Expr>
     */
    final protected function allClasses(): AllClasses
    {
        return AllClasses::allClasses();
    }

    /**
     * @return AllClasses<ExprScript>
     */
    final protected function allScripts(): AllClasses
    {
        return AllClasses::allScripts();
    }

    /**
     * @no-named-arguments
     */
    final protected static function assertRulesPass(
        RuleBuilder $ruleBuilder,
        string $message,
    ): void {
        $dispatcher = new EventDispatcher();

        $builder = new AssertBuilder();
        $dispatcher->addSubscriber($builder);

        $executeService = new ExecuteService($dispatcher, $ruleBuilder->getRuleObject());
        $executeService->assert();

        $assert = $builder->getAssertValueObject();

        foreach ($assert->pass as $key => $value) {
            Assert::assertTrue(
                (bool) $value,
                implode(', ', $assert->violations[$key] ?? []),
            );
            Assert::assertSame($key, $message);
        }
    }

    /**
     * @param array<int,string>|string $message
     * @param array<int,int>|int $line
     */
    final protected static function assertRulesViolation(
        RuleBuilder $ruleBuilder,
        array|string $message,
        array|int $line = 1,
    ): void {
        $dispatcher = new EventDispatcher();

        $builder = new AssertBuilder();
        $dispatcher->addSubscriber($builder);

        $executeService = new ExecuteService($dispatcher, $ruleBuilder->getRuleObject());
        $executeService->assert();

        $assert = $builder->getAssertValueObject();

        foreach ($assert->pass as $key => $value) {
            Assert::assertFalse((bool) $value);
            Assert::assertSame(
                is_string($message)
                    ? implode(', ', $assert->violations[$key] ?? [])
                    : array_column($assert->violations[$key], 'messageViolation'),
                $message,
            );
            Assert::assertSame(
                is_int($line)
                    ? $assert->violations[$key][0]->line
                    : array_column($assert->violations[$key], 'line'),
                $line,
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Helper;

use PHPUnit\Framework\Assert;
use StructuraPhp\Structura\Builder\AllClasses;
use StructuraPhp\Structura\Builder\RuleBuilder;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Services\ExecuteService;

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
        $executeService = new ExecuteService($ruleBuilder->getRuleObject());
        $assert = $executeService->assert()->getAssertValueObject();

        foreach ($assert->pass as $key => $value) {
            Assert::assertTrue(
                (bool) $value,
                implode(', ', $assert->violations[$key] ?? []),
            );
            Assert::assertSame($key, $message);
        }
    }

    final protected static function assertRulesViolation(
        RuleBuilder $ruleBuilder,
        string $message,
    ): void {
        $executeService = new ExecuteService($ruleBuilder->getRuleObject());
        $assert = $executeService->assert()->getAssertValueObject();

        foreach ($assert->pass as $key => $value) {
            Assert::assertFalse((bool) $value, $message);
            Assert::assertSame(
                implode(', ', $assert->violations[$key] ?? []),
                $message,
            );
        }
    }
}

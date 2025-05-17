<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Helper;

use PHPUnit\Framework\Assert;
use StructuraPhp\Structura\Builder\AllClasses;
use StructuraPhp\Structura\Builder\RuleBuilder;
use StructuraPhp\Structura\Services\ExecuteService;

trait ArchitectureAsserts
{
    final protected function allClasses(): AllClasses
    {
        return new AllClasses();
    }

    /**
     * @no-named-arguments
     */
    final protected static function assertRulesPass(RuleBuilder $ruleBuilder): void
    {
        $executeService = new ExecuteService($ruleBuilder->getRuleObject());
        $assertBuilder = $executeService->assert();

        $violations = $assertBuilder->getViolations();
        foreach ($assertBuilder->getPass() as $key => $value) {
            Assert::assertTrue(
                (bool) $value,
                implode(', ', $violations[$key] ?? []),
            );
        }
    }

    final protected static function assertRulesViolation(RuleBuilder $ruleBuilder, string $message): void
    {
        $executeService = new ExecuteService($ruleBuilder->getRuleObject());
        $assertBuilder = $executeService->assert();

        $violations = $assertBuilder->getViolations();
        foreach ($assertBuilder->getPass() as $key => $value) {
            Assert::assertFalse((bool) $value);
            Assert::assertSame(implode(', ', $violations[$key] ?? []), $message);
        }
    }
}

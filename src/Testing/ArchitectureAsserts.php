<?php

declare(strict_types=1);

namespace Structura\Testing;

use PHPUnit\Framework\Assert;
use Structura\Builder\AllClasses;
use Structura\Builder\RuleBuilder;
use Structura\Services\ExecuteService;

trait ArchitectureAsserts
{
    final protected function allClasses(): AllClasses
    {
        return new AllClasses();
    }

    /**
     * @no-named-arguments
     */
    final protected static function assertRules(RuleBuilder $ruleBuilder): void
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
}

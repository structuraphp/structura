<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use ReflectionClass;
use ReflectionMethod;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;

/**
 * @phpstan-import-type ViolationsByTest from AnalyseValueObject
 */
final class AnalyseService
{
    private int $countPass = 0;

    private int $countViolation = 0;

    private int $countWarning = 0;

    /** @var array<int,AnalyseTestValueObject> $analyseTestValueObjects  */
    private array $analyseTestValueObjects = [];

    /** @var array<int,ViolationsByTest> */
    private array $violationsByTests = [];

    public function __construct(
        private readonly StructuraConfig $structuraConfig,
    ) {
    }

    public function analyse(): AnalyseValueObject
    {
        $timeStart = microtime(true);

        /** @var class-string<TestBuilder> $ruleClassname */
        foreach ($this->structuraConfig->getRules() as $ruleClassname) {
            $this->executeTests($ruleClassname);
        }

        return new AnalyseValueObject(
            timeStart: $timeStart,
            countPass: $this->countPass,
            countViolation: $this->countViolation,
            countWarning: $this->countWarning,
            violationsByTests: $this->violationsByTests,
            analyseTestValueObjects: $this->analyseTestValueObjects,
        );
    }

    /**
     * @param class-string<TestBuilder> $classname
     */
    private function executeTests(string $classname): void
    {
        $class = new ReflectionClass($classname);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        $instance = new $classname();
        foreach ($methods as $method) {
            $attributes = $method->getAttributes(TestDox::class);
            if (\count($attributes) !== 1) {
                continue;
            }

            /** @var string $testDox */
            $testDox = $attributes[0]->getArguments()[0];

            /** @var callable $callable */
            $callable = [$instance, $method->getName()];
            // build test
            \call_user_func($callable);

            $this->executeAssertions($instance, $testDox, $classname);
        }
    }

    private function executeAssertions(
        TestBuilder $instance,
        string $testDox,
        string $classname,
    ): void {
        foreach ($instance->getRules() as $expectationFilter) {
            $ruleValueObject = $expectationFilter->getRuleBuilder()->getRuleObject();
            $executeService = new ExecuteService($ruleValueObject);
            $assertBuilder = $executeService->assert();

            $this->countPass += $assertBuilder->countAssertsSuccess();
            $this->countViolation += $assertBuilder->countAssertsFailure();
            $this->countWarning += $assertBuilder->countAssertsWarning();

            $this->analyseTestValueObjects[] = new AnalyseTestValueObject(
                textDox: $testDox,
                classname: $classname,
                ruleValueObject: $ruleValueObject,
                assertBuilder: $assertBuilder,
            );

            $violations = $assertBuilder->getViolations();

            if ($violations !== []) {
                $this->violationsByTests[] = $violations;
            }
        }
    }
}

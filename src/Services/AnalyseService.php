<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use ReflectionClass;
use ReflectionMethod;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Builder\AssertBuilder;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Finder\Finder;

/**
 * @phpstan-import-type ViolationsByTest from AnalyseValueObject
 */
final class AnalyseService
{
    private int $countPass = 0;

    private int $countViolation = 0;

    private int $countWarning = 0;

    /** @var array<int,string> */
    private array $prints = [];

    /** @var array<int,ViolationsByTest> */
    private array $violationsByTests = [];

    public function __construct(
        private readonly StructuraConfig $structuraConfig,
    ) {}

    public function analyse(): AnalyseValueObject
    {
        /** @var class-string<TestBuilder> $ruleClassname */
        foreach ($this->structuraConfig->getRules() as $ruleClassname) {
            $this->executeTests($ruleClassname);
        }

        return new AnalyseValueObject(
            countPass: $this->countPass,
            countViolation: $this->countViolation,
            countWarning: $this->countWarning,
            violationsByTests: $this->violationsByTests,
            prints: $this->prints,
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

            $this->prints[] = \sprintf(
                '%s %s in %s',
                $assertBuilder->countAssertsFailure() === 0
                    ? '<pass> PASS </pass>'
                    : '<violation> ERROR </violation>',
                $testDox,
                $classname,
            );

            $this->fromOutput($ruleValueObject->finder);
            $this->thatOutput($ruleValueObject->that);
            $this->shouldOutput($assertBuilder);

            $this->prints[] = '';

            $violations = $assertBuilder->getViolations();

            if ($violations !== []) {
                $this->violationsByTests[] = $violations;
            }
        }
    }

    private function fromOutput(?Finder $finder): void
    {
        if ($finder instanceof Finder) {
            $this->prints[] = $finder->count() . ' classes from';
            $this->prints[] = ' - dirs';
        } else {
            $this->prints[] = 'Class from';
            $this->prints[] = ' - raw value';
        }
    }

    private function thatOutput(?Expr $builder): void
    {
        if (!$builder instanceof Expr) {
            return;
        }

        $this->prints[] = 'That';

        foreach ($builder as $expr) {
            $this->prints[] = \sprintf(' - %s', $expr);
        }
    }

    private function shouldOutput(AssertBuilder $assertBuilder): void
    {
        $this->prints[] = 'Should';

        foreach ($assertBuilder->getPass() as $message => $isPass) {
            if ($isPass === 0) {
                $this->prints[] = \sprintf(
                    ' <fire>✘</fire> %s <fire>%d error(s)</fire>',
                    $message,
                    $assertBuilder->countViolation($message),
                );
            } else {
                $countWarning = $assertBuilder->countWarning($message);
                $warning = $countWarning !== 0
                    ? sprintf(' <warning>%d warning(s)</warning>', $countWarning)
                    : '';

                $this->prints[] = \sprintf(
                    ' <green>✔</green> %s%s',
                    $message,
                    $warning,
                );
            }
        }
    }
}

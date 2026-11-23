<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use ReflectionClass;
use ReflectionMethod;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Builder\AssertBuilder;
use StructuraPhp\Structura\Exception\Console\EventException;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\RuleValueObject;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;

final class AnalyseService
{
    /** @var array<int,AnalyseTestValueObject> */
    private array $analyseTestValueObjects = [];

    /** @var array<int, string> lowercase alternatives of the filter, empty to run every test */
    private readonly array $filters;

    /**
     * @param array<string, string> $pathResolvers
     */
    public function __construct(
        private readonly AnalysisDispatcher $dispatcher,
        private readonly bool $stopOnError = false,
        private readonly bool $stopOnWarning = false,
        private readonly bool $stopOnNotice = false,
        private readonly ?string $filter = null,
        private readonly array $pathResolvers = [],
        private readonly ?BaselineService $baseline = null,
    ) {
        $this->filters = $this->splitFilter($this->filter);
    }

    /**
     * @param class-string<TestBuilder> $ruleClassname
     */
    public function analyse(
        float $timeStart,
        string $ruleClassname,
    ): AnalyseValueObject {
        if ($this->executeTests($ruleClassname)) {
            throw new StopOnException(
                $this->getAnalyseValueObject($timeStart),
            );
        }

        return $this->getAnalyseValueObject($timeStart);
    }

    /**
     * @param class-string<TestBuilder> $classname
     *
     * @return bool true when a stop-on option interrupted the analysis
     */
    private function executeTests(string $classname): bool
    {
        $matchClassname = $this->match($classname);

        $class = new ReflectionClass($classname);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        $fileName = $class->getFileName();
        $fileName = is_string($fileName) ? $fileName : '';

        $instance = new $classname();
        foreach ($methods as $method) {
            if (!$matchClassname && !$this->match($method->name)) {
                continue;
            }

            $attributes = $method->getAttributes(TestDox::class);
            if (\count($attributes) !== 1) {
                continue;
            }

            /** @var string $testDox */
            $testDox = $attributes[0]->getArguments()[0];

            $sourceTest = new SourceTestValueObject(
                testClassname: $classname,
                testDox: $testDox,
                methodName: $method->getName(),
                line: (int) $method->getStartLine(),
                pathname: $fileName,
            );

            $assertBuilder = new AssertBuilder();
            $this->dispatcher->addSubscriber($assertBuilder);
            $this->dispatcher->setCurrentSource($sourceTest);

            $ruleValueObjects = [];

            try {
                /** @var callable $callable */
                $callable = [$instance, $method->getName()];

                \call_user_func($callable);

                $ruleValueObjects = $this->executeAssertions($instance, $sourceTest);
            } catch (EventException $eventException) {
                $instance->getRules();

                $this->dispatcher->dispatch($eventException->event);
            } finally {
                $this->dispatcher->setCurrentSource(null);
                $this->dispatcher->removeSubscriber($assertBuilder);
            }

            $this->analyseTestValueObjects[] = new AnalyseTestValueObject(
                source: $sourceTest,
                ruleValueObjects: $ruleValueObjects,
                assertValueObject: $assertBuilder->getAssertValueObject(),
            );

            if ($this->isStopOn($assertBuilder)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, RuleValueObject>
     */
    private function executeAssertions(
        TestBuilder $instance,
        SourceTestValueObject $sourceTest,
    ): array {
        $ruleValueObjects = [];
        foreach ($instance->getRules() as $expectationFilter) {
            $expectationFilter->getRuleBuilder()->setPathResolvers($this->pathResolvers);
            $ruleValueObject = $expectationFilter->getRuleBuilder()->getRuleObject();
            $ruleValueObjects[] = $ruleValueObject;

            $executeService = new ExecuteService($this->dispatcher, $ruleValueObject, $sourceTest, $this->baseline);
            $executeService->assert();
        }

        return $ruleValueObjects;
    }

    private function getAnalyseValueObject(float $timeStart): AnalyseValueObject
    {
        $countPass = 0;
        $countViolation = 0;
        $countWarning = 0;
        $countNotice = 0;

        foreach ($this->analyseTestValueObjects as $testObj) {
            $countPass += $testObj->assertValueObject->countAssertsSuccess();
            $countViolation += $testObj->assertValueObject->countAssertsFailure();
            $countWarning += $testObj->assertValueObject->countAssertsWarning();
            $countNotice += $testObj->assertValueObject->countAssertsNotices();
        }

        return new AnalyseValueObject(
            timeStart: $timeStart,
            countPass: $countPass,
            countViolation: $countViolation,
            countWarning: $countWarning,
            countNotice: $countNotice,
            analyseTestValueObjects: $this->analyseTestValueObjects,
        );
    }

    /**
     * A name matches when it contains one of the alternatives of the filter, whatever the case.
     */
    private function match(string $str): bool
    {
        if ($this->filters === []) {
            return true;
        }

        $name = strtolower($str);
        foreach ($this->filters as $filter) {
            if (str_contains($name, $filter)) {
                return true;
            }
        }

        return false;
    }

    /**
     * "A|B" gives ["a", "b"]: no regex, each alternative is plain text. Empty ones are dropped,
     * so that "A|" does not run every test.
     *
     * @return array<int, string>
     */
    private function splitFilter(?string $filter): array
    {
        $filters = [];
        foreach (explode('|', $filter ?? '') as $alternative) {
            $alternative = strtolower(trim($alternative));
            if ($alternative !== '') {
                $filters[] = $alternative;
            }
        }

        return $filters;
    }

    private function isStopOn(AssertBuilder $assertBuilder): bool
    {
        $assert = $assertBuilder->getAssertValueObject();

        return ($this->stopOnError && $assert->countAssertsFailure() >= 1)
            || ($this->stopOnWarning && $assert->countAssertsWarning() >= 1)
            || ($this->stopOnNotice && $assert->countAssertsNotices() >= 1);
    }
}

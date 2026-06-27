<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use Psr\EventDispatcher\EventDispatcherInterface;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Builder\AssertBuilder;
use StructuraPhp\Structura\Exception\Console\EventException;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @phpstan-import-type ViolationsByTest from AnalyseValueObject
 * @phpstan-import-type WarningByTest from AnalyseValueObject
 */
final class AnalyseService
{
    /** @var array<int,AnalyseTestValueObject> */
    private array $analyseTestValueObjects = [];

    /** @var array<int,ViolationsByTest> */
    private array $violationsByTests = [];

    /** @var array<int, WarningByTest> */
    private array $warningsByTests = [];

    /** @var array<int, array<string, string>> */
    private array $noticeByTests = [];

    private AssertBuilder $assertBuilder;

    private EventDispatcherInterface $dispatcher;

    /**
     * @param array<string, string> $pathResolvers
     */
    public function __construct(
        private readonly bool $stopOnError = false,
        private readonly bool $stopOnWarning = false,
        private readonly bool $stopOnNotice = false,
        private readonly ?string $filter = null,
        private readonly array $pathResolvers = [],
    ) {
        $this->assertBuilder = new AssertBuilder();

        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->assertBuilder);
    }

    /**
     * @param class-string<TestBuilder> $ruleClassname
     */
    public function analyse(
        float $timeStart,
        string $ruleClassname,
    ): AnalyseValueObject {
        try {
            $this->executeTests($ruleClassname);
        } catch (RuntimeException) {
            throw new StopOnException(
                $this->getAnalyseValueObject($timeStart),
            );
        }

        return $this->getAnalyseValueObject($timeStart);
    }

    public function analyses(FinderService $finderService): AnalyseValueObject
    {
        $timeStart = microtime(true);
        $results = [];

        try {
            /** @var class-string<TestBuilder> $ruleClassname */
            foreach ($finderService->getClassTests() as $ruleClassname) {
                $service = new self(
                    stopOnError: $this->stopOnError,
                    stopOnWarning: $this->stopOnWarning,
                    stopOnNotice: $this->stopOnNotice,
                    filter: $this->filter,
                    pathResolvers: $this->pathResolvers,
                );

                try {
                    $results[] = $service->analyse($timeStart, $ruleClassname);
                } catch (StopOnException $stopOnException) {
                    $results[] = $stopOnException->analyseValueObject;
                    throw new RuntimeException();
                }
            }
        } catch (RuntimeException) {
            throw new StopOnException($this->mergeResults($timeStart, $results));
        }

        return $this->mergeResults($timeStart, $results);
    }

    /**
     * @param array<int, AnalyseValueObject> $results
     */
    private function mergeResults(float $timeStart, array $results): AnalyseValueObject
    {
        $countPass = 0;
        $countViolation = 0;
        $countWarning = 0;
        $countNotice = 0;
        $violationsByTests = [];
        $warningsByTests = [];
        $noticeByTests = [];
        $analyseTestValueObjects = [];

        foreach ($results as $result) {
            $countPass += $result->countPass;
            $countViolation += $result->countViolation;
            $countWarning += $result->countWarning;
            $countNotice += $result->countNotice;
            array_push($violationsByTests, ...$result->violationsByTests);
            array_push($warningsByTests, ...$result->warningsByTests);
            array_push($noticeByTests, ...$result->noticeByTests);
            array_push($analyseTestValueObjects, ...$result->analyseTestValueObjects);
        }

        return new AnalyseValueObject(
            timeStart: $timeStart,
            countPass: $countPass,
            countViolation: $countViolation,
            countWarning: $countWarning,
            countNotice: $countNotice,
            violationsByTests: $violationsByTests,
            warningsByTests: $warningsByTests,
            noticeByTests: $noticeByTests,
            analyseTestValueObjects: $analyseTestValueObjects,
        );
    }

    /**
     * @param class-string<TestBuilder> $classname
     */
    private function executeTests(string $classname): void
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
                classname: $classname,
                textDox: $testDox,
                methodName: $method->getName(),
                filePath: $fileName,
            );

            try {
                /** @var callable $callable */
                $callable = [$instance, $method->getName()];

                // build test
                \call_user_func($callable);

                $this->executeAssertions($instance, $sourceTest);
            } catch (EventException $eventException) {
                $instance->getRules();

                $this->dispatcher->dispatch($eventException->event);

                $this->isStopOn();

                continue;
            }
        }
    }

    private function executeAssertions(
        TestBuilder $instance,
        SourceTestValueObject $sourceTest,
    ): void {
        foreach ($instance->getRules() as $expectationFilter) {
            $expectationFilter->getRuleBuilder()->setPathResolvers($this->pathResolvers);
            $ruleValueObject = $expectationFilter->getRuleBuilder()->getRuleObject();

            $executeService = new ExecuteService($this->dispatcher, $ruleValueObject, $sourceTest);
            $executeService->assert();

            $assertValueObject = $this->assertBuilder->getAssertValueObject();

            $this->analyseTestValueObjects[] = new AnalyseTestValueObject(
                source: $sourceTest,
                ruleValueObject: $ruleValueObject,
                assertValueObject: $assertValueObject,
            );

            if ($assertValueObject->violations !== []) {
                $this->violationsByTests[] = $assertValueObject->violations;
            }

            if ($assertValueObject->warnings !== []) {
                $this->warningsByTests[] = $assertValueObject->warnings;
            }

            if ($assertValueObject->notices !== []) {
                $this->noticeByTests[] = $assertValueObject->notices;
            }

            $this->isStopOn();
        }
    }

    private function getAnalyseValueObject(float $timeStart): AnalyseValueObject
    {
        $assert = $this->assertBuilder->getAssertValueObject();

        return new AnalyseValueObject(
            timeStart: $timeStart,
            countPass: $assert->countAssertsSuccess(),
            countViolation: $assert->countAssertsFailure(),
            countWarning: $assert->countAssertsWarning(),
            countNotice: $assert->countAssertsNotices(),
            violationsByTests: $this->violationsByTests,
            warningsByTests: $this->warningsByTests,
            noticeByTests: $this->noticeByTests,
            analyseTestValueObjects: $this->analyseTestValueObjects,
        );
    }

    private function match(string $str): bool
    {
        return $this->filter === null
            || str_contains(
                strtolower(trim($str)),
                strtolower(trim($this->filter)),
            );
    }

    private function isStopOn(): void
    {
        $assert = $this->assertBuilder->getAssertValueObject();

        if ($this->stopOnError && $assert->countAssertsFailure() >= 1) {
            throw new RuntimeException();
        }

        if ($this->stopOnWarning && $assert->countAssertsWarning() >= 1) {
            throw new RuntimeException();
        }

        if ($this->stopOnNotice && $assert->countAssertsNotices() >= 1) {
            throw new RuntimeException();
        }
    }
}

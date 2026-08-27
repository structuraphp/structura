<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use ReflectionClass;
use ReflectionMethod;
use RuntimeException;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Builder\AssertBuilder;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Exception\Console\EventException;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\RuleDescriptionValueObject;
use StructuraPhp\Structura\ValueObjects\RuleValuesObject;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use Symfony\Component\Finder\Finder;

final class AnalyseService
{
    /** @var array<int,AnalyseTestValueObject> */
    private array $analyseTestValueObjects = [];

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
    ) {}

    /**
     * @param array<string, string> $pathResolvers
     */
    public static function create(
        bool $stopOnError = false,
        bool $stopOnWarning = false,
        bool $stopOnNotice = false,
        ?string $filter = null,
        array $pathResolvers = [],
    ): self {
        return new self(
            new AnalysisDispatcher(),
            $stopOnError,
            $stopOnWarning,
            $stopOnNotice,
            $filter,
            $pathResolvers,
        );
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
                testClassname: $classname,
                textDox: $testDox,
                methodName: $method->getName(),
                line: (int) $method->getStartLine(),
                pathname: $fileName,
            );

            $assertBuilder = new AssertBuilder();
            $this->dispatcher->addSubscriber($assertBuilder);
            $this->dispatcher->setCurrentSource($sourceTest);

            $ruleDescriptions = [];

            try {
                /** @var callable $callable */
                $callable = [$instance, $method->getName()];

                \call_user_func($callable);

                $ruleDescriptions = $this->executeAssertions($instance, $sourceTest);
            } catch (EventException $eventException) {
                $instance->getRules();

                $this->dispatcher->dispatch($eventException->event);
            } finally {
                $this->dispatcher->setCurrentSource(null);
                $this->dispatcher->removeSubscriber($assertBuilder);
            }

            $this->analyseTestValueObjects[] = new AnalyseTestValueObject(
                source: $sourceTest,
                ruleDescriptions: $ruleDescriptions,
                assertValueObject: $assertBuilder->getAssertValueObject(),
            );

            $this->isStopOn($assertBuilder);
        }
    }

    /**
     * @return array<int, RuleDescriptionValueObject>
     */
    private function executeAssertions(
        TestBuilder $instance,
        SourceTestValueObject $sourceTest,
    ): array {
        $ruleDescriptions = [];
        foreach ($instance->getRules() as $expectationFilter) {
            $expectationFilter->getRuleBuilder()->setPathResolvers($this->pathResolvers);
            $ruleValueObject = $expectationFilter->getRuleBuilder()->getRuleObject();

            $executeService = new ExecuteService($this->dispatcher, $ruleValueObject, $sourceTest);
            $executeService->assert();

            $ruleDescriptions[] = $this->describeRule($ruleValueObject);
        }

        return $ruleDescriptions;
    }

    /**
     * Projects a rule onto the serializable subset progress formatters actually consume.
     */
    private function describeRule(RuleValuesObject $ruleValueObject): RuleDescriptionValueObject
    {
        $finder = $ruleValueObject->finder;
        $that = $ruleValueObject->that;

        $thatExpressions = $that instanceof AbstractExpr
            ? array_map(
                static fn (AbstractExpr|ExprInterface $expr): string => (string) $expr,
                iterator_to_array($that, false),
            )
            : null;

        return new RuleDescriptionValueObject(
            sourceCount: $finder instanceof Finder
                ? $finder->count()
                : \count($ruleValueObject->raws),
            fromFinder: $finder instanceof Finder,
            thatExpressions: $thatExpressions,
        );
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

    private function match(string $str): bool
    {
        return $this->filter === null
            || str_contains(
                strtolower(trim($str)),
                strtolower(trim($this->filter)),
            );
    }

    private function isStopOn(AssertBuilder $assertBuilder): void
    {
        $assert = $assertBuilder->getAssertValueObject();

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

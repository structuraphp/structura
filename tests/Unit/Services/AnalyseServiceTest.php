<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Events\NoticeEvent;
use StructuraPhp\Structura\Events\ViolationEvent;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Services\AnalyseService;
use StructuraPhp\Structura\Services\AnalysisDispatcher;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\Testing\TestBuilder;
use StructuraPhp\Structura\Tests\Feature\TestAssert;
use StructuraPhp\Structura\Tests\Feature\TestConfig;
use StructuraPhp\Structura\Tests\Feature\TestController;
use StructuraPhp\Structura\Tests\Feature\TestEmpty;
use StructuraPhp\Structura\Tests\Helper\OneViolationTestBuilder;
use StructuraPhp\Structura\Tests\Helper\ThrowingTestBuilder;
use StructuraPhp\Structura\Tests\Helper\TwoRulesTestBuilder;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use Symfony\Component\EventDispatcher\EventDispatcher;
use UnexpectedValueException;

#[CoversClass(AnalyseService::class)]
final class AnalyseServiceTest extends TestCase
{
    protected function setUp(): void
    {
        $config = StructuraConfig::make()
            ->addTestSuite('tests/Feature', 'main')
            ->getConfig();

        (new FinderService($config))->getClassTests();
    }

    public function testAnalyseSingleClass(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher());
        $result = $service->analyse(microtime(true), TestConfig::class);

        self::assertSame(0, $result->countViolation);
        self::assertSame(1, $result->countPass);
        self::assertSame(0, $result->countWarning);
        self::assertSame(0, $result->countNotice);
    }

    public function testAnalyseSingleClassWithFilter(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher(), filter: 'nonexistent');
        $result = $service->analyse(microtime(true), TestConfig::class);

        self::assertSame(0, $result->countViolation);
        self::assertSame(0, $result->countPass);
    }

    public function testStopOnError(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher(), stopOnError: true);

        $this->expectException(StopOnException::class);
        $service->analyse(microtime(true), TestAssert::class);
    }

    public function testStopOnErrorAtFirstViolation(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher(), stopOnError: true);

        $this->expectException(StopOnException::class);
        $service->analyse(microtime(true), OneViolationTestBuilder::class);
    }

    public function testStopOnWarning(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher(), stopOnWarning: true);

        $this->expectException(StopOnException::class);
        $service->analyse(microtime(true), TestAssert::class);
    }

    public function testStopOnNotice(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher(), stopOnNotice: true);

        $this->expectException(StopOnException::class);
        $service->analyse(microtime(true), TestEmpty::class);
    }

    public function testNoStopWithoutStopOnOption(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher());
        $result = $service->analyse(microtime(true), TestAssert::class);

        self::assertSame(2, $result->countViolation);
        self::assertSame(1, $result->countWarning);
    }

    public function testStopOnWarningAndNoticeIgnoreErrors(): void
    {
        $service = new AnalyseService(
            new AnalysisDispatcher(),
            stopOnWarning: true,
            stopOnNotice: true,
        );
        $result = $service->analyse(microtime(true), TestController::class);

        self::assertSame(3, $result->countViolation);
    }

    public function testRuntimeExceptionFromTestIsNotTakenForStopOn(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher());

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Rule definition crashed');

        $service->analyse(microtime(true), ThrowingTestBuilder::class);
    }

    public function testFilterMatchesClassnameCaseInsensitivelyAndTrimmed(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher(), filter: ' feature\TESTconfig ');
        $result = $service->analyse(microtime(true), TestConfig::class);

        self::assertSame(1, $result->countPass);
    }

    public function testFilterMatchesMethodAfterNonMatchingOne(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher(), filter: 'emptyArchitecture');
        $result = $service->analyse(microtime(true), TestEmpty::class);

        self::assertCount(1, $result->analyseTestValueObjects);
        self::assertSame('testEmptyArchitecture', $result->analyseTestValueObjects[0]->source->methodName);
    }

    /**
     * @param class-string<TestBuilder> $classname
     * @param array<int, string> $methods
     */
    #[DataProvider('getFilterProvider')]
    public function testFilterAlternatives(string $filter, string $classname, array $methods): void
    {
        $service = new AnalyseService(new AnalysisDispatcher(), filter: $filter);
        $result = $service->analyse(microtime(true), $classname);

        self::assertSame(
            $methods,
            array_map(
                static fn (AnalyseTestValueObject $test): string => $test->source->methodName,
                $result->analyseTestValueObjects,
            ),
        );
    }

    /**
     * @return Generator<string, array{string, class-string<TestBuilder>, array<int, string>}>
     */
    public static function getFilterProvider(): Generator
    {
        $config = ['testAssertArchitectureRules'];
        $empty = ['testVoidArchitecture', 'testEmptyArchitecture'];

        yield 'alternatives matching the class' => ['TestController|TestConfig', TestConfig::class, $config];

        yield 'alternatives matching another class' => ['TestController|TestConfig', TestEmpty::class, []];

        yield 'alternatives of methods' => ['testVoidArchitecture|testEmptyArchitecture', TestEmpty::class, $empty];

        yield 'alternatives with spaces' => [' TestController | TestConfig ', TestConfig::class, $config];

        yield 'empty alternative does not run every test' => ['TestController|', TestEmpty::class, []];

        yield 'parentheses are plain text' => ['(TestController|TestConfig)', TestConfig::class, []];

        yield 'class name with namespace' => ['Tests\Feature\TestConfig', TestConfig::class, $config];

        yield 'regex characters are plain text' => ['^testVoid', TestEmpty::class, []];
    }

    public function testSkipsPublicMethodWithoutTestDoxAndKeepsAllRules(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher());
        $result = $service->analyse(microtime(true), TwoRulesTestBuilder::class);

        self::assertCount(1, $result->analyseTestValueObjects);
        self::assertCount(2, $result->analyseTestValueObjects[0]->ruleValueObjects);
    }

    public function testNoticesDoNotStopByDefaultAndRulesAreResetAfterEventException(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher());
        $result = $service->analyse(microtime(true), TestEmpty::class);

        self::assertSame(2, $result->countNotice);
        self::assertCount(0, $result->analyseTestValueObjects[0]->ruleValueObjects);
        self::assertCount(1, $result->analyseTestValueObjects[1]->ruleValueObjects);
    }

    public function testCountsAreSummedOverAllAnalysedTests(): void
    {
        $service = new AnalyseService(new AnalysisDispatcher());
        $service->analyse(microtime(true), TestAssert::class);

        $result = $service->analyse(microtime(true), TestEmpty::class);

        self::assertSame(5, $result->countPass);
        self::assertSame(2, $result->countViolation);
        self::assertSame(1, $result->countWarning);
        self::assertSame(2, $result->countNotice);
    }

    public function testEventsCarryTheirSourceOnlyDuringTheTest(): void
    {
        $events = [];
        $parent = new EventDispatcher();
        $parent->addListener(ViolationEvent::class, static function (ViolationEvent $event) use (&$events): void {
            $events[] = $event;
        });

        $dispatcher = new AnalysisDispatcher($parent);
        (new AnalyseService($dispatcher))->analyse(microtime(true), OneViolationTestBuilder::class);

        self::assertCount(1, $events);
        self::assertSame(OneViolationTestBuilder::class, $events[0]->source?->testClassname);
        self::assertFalse($dispatcher->hasListeners());

        $after = $dispatcher->dispatch(new NoticeEvent(key: 'key', message: 'message'));
        self::assertNull($after->source);
    }

    public function testDispatcherIsCleanedUpWhenTestThrows(): void
    {
        $dispatcher = new AnalysisDispatcher();

        try {
            (new AnalyseService($dispatcher))->analyse(microtime(true), ThrowingTestBuilder::class);
            self::fail('Expected exception');
        } catch (UnexpectedValueException) {
        }

        self::assertFalse($dispatcher->hasListeners());

        $after = $dispatcher->dispatch(new NoticeEvent(key: 'key', message: 'message'));
        self::assertNull($after->source);
    }

    public function testDispatcherInjectsSource(): void
    {
        $dispatcher = new AnalysisDispatcher();
        $service = new AnalyseService($dispatcher);
        $result = $service->analyse(microtime(true), TestConfig::class);

        self::assertNotCount(0, $result->analyseTestValueObjects);
        self::assertSame(TestConfig::class, $result->analyseTestValueObjects[0]->source->testClassname);
    }
}

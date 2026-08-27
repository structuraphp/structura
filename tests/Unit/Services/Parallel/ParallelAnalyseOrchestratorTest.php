<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Console\Enums\AnalyseOption;
use StructuraPhp\Structura\Console\Enums\CommonOption;
use StructuraPhp\Structura\Contracts\AnalyseOrchestratorInterface;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Formatter\Progress\ProgressTextFormatter;
use StructuraPhp\Structura\Services\AnalyseOrchestrator;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\Services\Parallel\AnalyseResultSerializer;
use StructuraPhp\Structura\Services\Parallel\OrderedResultCollector;
use StructuraPhp\Structura\Services\Parallel\ParallelAnalyseOrchestrator;
use StructuraPhp\Structura\Services\Parallel\WorkerPool;
use StructuraPhp\Structura\Tests\Helper\AnalyseResultRecorder;
use StructuraPhp\Structura\Tests\Helper\OutputFormatter;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * The contract of the parallel orchestrator is that it is indistinguishable from the sequential
 * one: same counters, same progress output, same stop-on truncation. Every test here therefore
 * compares a parallel run against a sequential run of the very same suite.
 */
#[CoversClass(ParallelAnalyseOrchestrator::class)]
#[CoversClass(WorkerPool::class)]
#[CoversClass(OrderedResultCollector::class)]
#[CoversClass(AnalyseResultSerializer::class)]
final class ParallelAnalyseOrchestratorTest extends TestCase
{
    private FinderService $finder;

    protected function setUp(): void
    {
        $config = StructuraConfig::make()
            ->addTestSuite('tests/Feature', 'main')
            ->getConfig();

        $this->finder = new FinderService($config);
    }

    #[TestWith([2])]
    #[TestWith([4])]
    #[TestWith([8])]
    public function testCountersMatchSequentialRun(int $processes): void
    {
        $expected = (new AnalyseOrchestrator())->run($this->finder);
        $actual = $this->parallel($processes)->run($this->finder);

        self::assertSame($expected->countPass, $actual->countPass);
        self::assertSame($expected->countViolation, $actual->countViolation);
        self::assertSame($expected->countWarning, $actual->countWarning);
        self::assertSame($expected->countNotice, $actual->countNotice);
    }

    /**
     * More workers than classes must not spawn idle noise nor lose a class.
     */
    public function testMoreProcessesThanClasses(): void
    {
        $expected = (new AnalyseOrchestrator())->run($this->finder);
        $actual = $this->parallel(32)->run($this->finder);

        self::assertCount(
            \count($expected->analyseTestValueObjects),
            $actual->analyseTestValueObjects,
        );
    }

    #[TestWith([2])]
    #[TestWith([4])]
    public function testProgressOutputIsIdenticalToSequentialRun(int $processes): void
    {
        $expected = $this->render((new AnalyseOrchestrator())->run($this->finder));
        $actual = $this->render($this->parallel($processes)->run($this->finder));

        self::assertSame($expected, $actual);
    }

    /**
     * Workers finish out of order, so this is what proves the sliding window actually reorders.
     */
    #[TestWith([2])]
    #[TestWith([4])]
    public function testResultsAreEmittedInDeclarationOrder(int $processes): void
    {
        $parallel = new AnalyseResultRecorder();
        $this->parallel($processes)->run($this->finder, $parallel->record(...));

        $sequential = new AnalyseResultRecorder();
        (new AnalyseOrchestrator())->run($this->finder, $sequential->record(...));

        self::assertSame($sequential->classnames(), $parallel->classnames());
        self::assertNotSame([], $parallel->classnames());
    }

    /**
     * A worker tripping a threshold must truncate exactly where the sequential run stops, which
     * means draining the classes already in flight rather than killing them.
     *
     * @param array<int, string> $stopOptions
     */
    #[DataProvider('stopOnProvider')]
    public function testStopOnTruncatesLikeSequentialRun(array $stopOptions): void
    {
        $sequentialOrchestrator = new AnalyseOrchestrator(
            stopOnError: \in_array(AnalyseOption::StopOnError->value, $stopOptions, true),
            stopOnWarning: \in_array(AnalyseOption::StopOnWarning->value, $stopOptions, true),
            stopOnNotice: \in_array(AnalyseOption::StopOnNotice->value, $stopOptions, true),
        );

        $expected = null;

        try {
            $sequentialOrchestrator->run($this->finder);
        } catch (StopOnException $stopOnException) {
            $expected = $stopOnException->analyseValueObject;
        }

        self::assertInstanceOf(AnalyseValueObject::class, $expected, 'the suite is expected to trip the threshold');

        $actual = null;

        try {
            $this->parallel(4, $stopOptions)->run($this->finder);
        } catch (StopOnException $stopOnException) {
            $actual = $stopOnException->analyseValueObject;
        }

        self::assertInstanceOf(AnalyseValueObject::class, $actual);
        self::assertSame($this->render($expected), $this->render($actual));
        self::assertSame($expected->countViolation, $actual->countViolation);
        self::assertSame($expected->countPass, $actual->countPass);
    }

    /**
     * @return array<string, array<int, array<int, string>>>
     */
    public static function stopOnProvider(): array
    {
        return [
            'stop on error' => [[AnalyseOption::StopOnError->value]],
            'stop on warning' => [[AnalyseOption::StopOnWarning->value]],
            'stop on notice' => [[AnalyseOption::StopOnNotice->value]],
        ];
    }

    public function testEmptyTestSuiteReturnsEmptyResult(): void
    {
        $config = StructuraConfig::make()->getConfig();
        $result = $this->parallel(4)->run(new FinderService($config, 'unknown'));

        self::assertSame([], $result->analyseTestValueObjects);
        self::assertSame(0, $result->countPass);
    }

    /**
     * @param array<int, string> $stopOptions
     */
    private function parallel(int $processes, array $stopOptions = []): AnalyseOrchestratorInterface
    {
        $options = ['--' . CommonOption::Config->value . '=' . getcwd() . '/structura.php'];
        foreach ($stopOptions as $stopOption) {
            $options[] = '--' . $stopOption;
        }

        return new ParallelAnalyseOrchestrator($processes, $options);
    }

    private function render(AnalyseValueObject $result): string
    {
        $buffer = new BufferedOutput(formatter: new OutputFormatter());
        $buffer->setDecorated(true);

        (new ProgressTextFormatter())->progressAdvance($buffer, $result);

        return $buffer->fetch();
    }
}

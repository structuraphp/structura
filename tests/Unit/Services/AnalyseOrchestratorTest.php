<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Formatter\Progress\ProgressTextFormatter;
use StructuraPhp\Structura\Services\AnalyseOrchestrator;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\Tests\Helper\OutputFormatter;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(AnalyseOrchestrator::class)]
final class AnalyseOrchestratorTest extends TestCase
{
    private FinderService $finder;

    protected function setUp(): void
    {
        $config = StructuraConfig::make()
            ->addTestSuite('tests/Feature', 'main')
            ->getConfig();

        $this->finder = new FinderService($config);
    }

    public function testRun(): void
    {
        $orchestrator = new AnalyseOrchestrator();
        $result = $orchestrator->run($this->finder);
        $formatter = new ProgressTextFormatter();

        $buffer = new BufferedOutput(formatter: new OutputFormatter());
        $buffer->setDecorated(true);

        $formatter->progressAdvance($buffer, $result);

        self::assertSame(5, $result->countViolation);
        self::assertSame(13, $result->countPass);
        self::assertSame(1, $result->countWarning);

        $expected = <<<'EOF'
        <violation> ERROR </violation> Asserts architecture rules in StructuraPhp\Structura\Tests\Feature\TestAssert
        52 classe(s) from
         - dirs
        That
         - to implement <promote>StructuraPhp\Structura\Contracts\ExprInterface</promote>
        Should
         <green>✔</green> to be classes
         <fire>✘</fire> to not depends on these namespaces <promote>StructuraPhp\Structura\ValueObjects\ClassDescription</promote> <fire>38 error(s)</fire>
         <green>✔</green> to have method <promote>__toString</promote>
         <green>✔</green> to use declare <promote>strict_types=1</promote>
         <green>✔</green> to have prefix <promote>To</promote> <yellow>1 warning(s)</yellow>
         <green>✔</green> to extend nothing
         <fire>✘</fire> to not use trait <fire>7 error(s)</fire>
         <green>✔</green> to have method <promote>__construct</promote>

        <pass> PASS </pass> Binary architecture rules in StructuraPhp\Structura\Tests\Feature\TestConfig
        1 classe(s) from
         - dirs
        Should
         <green>✔</green> to use declare <promote>strict_types=1</promote>

        <violation> ERROR </violation> Controllers architecture rules in StructuraPhp\Structura\Tests\Feature\TestController
        3 classe(s) from
         - dirs
        Should
         <green>✔</green> to be classes
         <green>✔</green> to use declare <promote>strict_types=1</promote>
         <fire>✘</fire> to not use trait <fire>1 error(s)</fire>
         <green>✔</green> to have suffix <promote>Controller</promote>
         <green>✔</green> to extend <promote>StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase</promote>
         <fire>✘</fire> to have method <promote>__construct</promote> <fire>3 error(s)</fire>
         <fire>✘</fire> depends only on these namespaces <promote>StructuraPhp\Structura\Tests\Fixture\Http\Controller\RoleController, StructuraPhp\Structura\Tests\Fixture\Models\User</promote> <fire>1 error(s)</fire>
         <green>✔</green> to use trait on these namespaces <promote>StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory</promote>
         <green>✔</green> depends only on inheritance <promote>StructuraPhp\Structura\Tests\Fixture\Contract\ShouldQueueInterface</promote>

        <pass> PASS </pass> Void architecture rules in StructuraPhp\Structura\Tests\Feature\TestEmpty
        Should
         <orange>◎</orange> Directory not found: "tests/Fixture/Void". Assertions were skipped.

        <pass> PASS </pass> Empty architecture rules in StructuraPhp\Structura\Tests\Feature\TestEmpty
        0 classe(s) from
         - dirs
        That
        Should
         <orange>◎</orange> No PHP files found for test "<promote>StructuraPhp\Structura\Tests\Feature\TestEmpty</promote>". Assertions were skipped.

        <pass> PASS </pass> Exceptions architecture rules in StructuraPhp\Structura\Tests\Feature\TestException
        2 classe(s) from
         - raw value
        Should
         <green>✔</green> to extend <promote>InvalidArgumentException</promote>
           | to extend <promote>Exception</promote>
           | to extend <promote>DomainException</promote>
             & to extend <promote>BadMethodCallException</promote>

        <pass> PASS </pass> Asserts architecture rules in StructuraPhp\Structura\Tests\Feature\TestVoid
        158 classe(s) from
         - dirs
        That
        Should

        EOF;

        $expected = explode(PHP_EOL, $expected);

        $fetch = explode(PHP_EOL, $buffer->fetch());

        foreach ($expected as $key => $line) {
            self::assertSame($line, $fetch[$key]);
        }
    }

    public function testStopOnError(): void
    {
        $orchestrator = new AnalyseOrchestrator(stopOnError: true);

        try {
            $orchestrator->run($this->finder);
        } catch (StopOnException $stopOnException) {
            $result = $stopOnException->analyseValueObject;

            self::assertSame(2, $result->countViolation);
            self::assertSame(5, $result->countPass);
            self::assertSame(1, $result->countWarning);
            self::assertSame(0, $result->countNotice);
        }
    }

    public function testStopOnWarning(): void
    {
        $orchestrator = new AnalyseOrchestrator(stopOnWarning: true);

        try {
            $orchestrator->run($this->finder);
        } catch (StopOnException $stopOnException) {
            $result = $stopOnException->analyseValueObject;

            self::assertSame(2, $result->countViolation);
            self::assertSame(5, $result->countPass);
            self::assertSame(1, $result->countWarning);
            self::assertSame(0, $result->countNotice);
        }
    }

    #[TestWith(['TestConfig'])]
    #[TestWith(['  TestConfig  '])]
    #[TestWith(['testconfig'])]
    public function testFilter(string $filter): void
    {
        $orchestrator = new AnalyseOrchestrator(filter: $filter);
        $result = $orchestrator->run($this->finder);

        self::assertSame(0, $result->countViolation);
        self::assertSame(1, $result->countPass);
        self::assertSame(0, $result->countWarning);
        self::assertSame(0, $result->countNotice);
    }

    public function testOnClassAnalysedCallback(): void
    {
        $called = 0;
        $orchestrator = new AnalyseOrchestrator();
        $orchestrator->run($this->finder, static function () use (&$called): void {
            $called++;
        });

        self::assertSame(count($this->finder->getClassTests()), $called);
    }
}

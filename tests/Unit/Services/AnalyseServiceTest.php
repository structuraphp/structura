<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Formatter\Progress\ProgressTextFormatter;
use StructuraPhp\Structura\Services\AnalyseService;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\Tests\Helper\OutputFormatter;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(AnalyseService::class)]
final class AnalyseServiceTest extends TestCase
{
    public function testAnalyseService(): void
    {
        $config = StructuraConfig::make()
            ->archiRootNamespace(
                'StructuraPhp\Structura\Tests\Feature',
                'tests/Feature',
            )
            ->getConfig();

        $finder = new FinderService($config);

        $service = new AnalyseService();

        $result = $service->analyses($finder);
        $formatter = new ProgressTextFormatter();

        $buffer = new BufferedOutput(formatter: new OutputFormatter());
        $buffer->setDecorated(true);

        $formatter->progressAdvance($buffer, $result);

        self::assertSame(5, $result->countViolation);
        self::assertSame(12, $result->countPass);
        self::assertSame(1, $result->countWarning);

        $expected = <<<'EOF'
        <violation> ERROR </violation> Asserts architecture rules in StructuraPhp\Structura\Tests\Feature\TestAssert
        40 classe(s) from
         - dirs
        That
         - to implement <promote>StructuraPhp\Structura\Contracts\ExprInterface</promote>
        Should
         <green>✔</green> to be classes
         <fire>✘</fire> to not depends on these namespaces <promote>StructuraPhp\Structura\ValueObjects\ClassDescription</promote> <fire>34 error(s)</fire>
         <green>✔</green> to have method <promote>__toString</promote>
         <green>✔</green> to use declare <promote>strict_types=1</promote>
         <green>✔</green> to have prefix <promote>To</promote> <yellow>1 warning(s)</yellow>
         <green>✔</green> to extend nothing
         <fire>✘</fire> to not use trait <fire>7 error(s)</fire>
         <green>✔</green> to have method <promote>__construct</promote>

        <violation> ERROR </violation> Controllers architecture rules in StructuraPhp\Structura\Tests\Feature\TestController
        3 classe(s) from
         - dirs
        Should
         <green>✔</green> to be classes
         <green>✔</green> to use declare <promote>strict_types=1</promote>
         <fire>✘</fire> to not use trait <fire>1 error(s)</fire>
         <green>✔</green> to have suffix <promote>Controller</promote>
         <green>✔</green> to extend <promote>StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase</promote>
         <fire>✘</fire> to have method <promote>__construct</promote> <fire>2 error(s)</fire>
         <fire>✘</fire> depends only on these namespaces <promote>StructuraPhp\Structura\Tests\Fixture\Http\Controller\RoleController, StructuraPhp\Structura\Tests\Fixture\Models\User</promote> <fire>1 error(s)</fire>
         <green>✔</green> to use trait on these namespaces <promote>StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory</promote>
         <green>✔</green> depends only on inheritance <promote>StructuraPhp\Structura\Tests\Fixture\Contract\ShouldQueueInterface</promote>

        <pass> PASS </pass> Exceptions architecture rules in StructuraPhp\Structura\Tests\Feature\TestException
        2 classe(s) from
         - raw value
        Should
         <green>✔</green> to extend <promote>InvalidArgumentException</promote>
           | to extend <promote>Exception</promote>
           | to extend <promote>DomainException</promote>
             & to extend <promote>BadMethodCallException</promote>

        <pass> PASS </pass> Asserts architecture rules in StructuraPhp\Structura\Tests\Feature\TestVoid
        119 classe(s) from
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
}

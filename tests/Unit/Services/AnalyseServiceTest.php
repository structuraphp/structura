<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Services\AnalyseService;

#[CoversClass(AnalyseService::class)]
final class AnalyseServiceTest extends TestCase
{
    public function testAnalyseService(): void
    {
        $service = new AnalyseService(
            StructuraConfig::make()
                ->archiRootNamespace(
                    'StructuraPhp\Structura\Tests\Feature',
                    'tests/Feature',
                ),
        );

        $result = $service->analyse();

        self::assertSame(5, $result->countViolation);
        self::assertSame(10, $result->countPass);
        self::assertSame(1, $result->countWarning);

        $expected = <<<'EOF'
        <violation> ERROR </violation> Asserts architecture rules in StructuraPhp\Structura\Tests\Feature\TestAssert
        39 classes from
         - dirs
        That
         - to implement <promote>StructuraPhp\Structura\Contracts\ExprInterface</promote>
        Should
         <green>✔</green> to be classes
         <fire>✘</fire> to not depends on these namespaces <promote>StructuraPhp\Structura\ValueObjects\ClassDescription</promote> <fire>38 error(s)</fire>
         <green>✔</green> to have method <promote>__toString</promote>
         <green>✔</green> to use declare <promote>strict_types=1</promote>
         <green>✔</green> to have prefix <promote>To</promote> <warning>1 warning(s)</warning>
         <green>✔</green> to extend nothing
         <fire>✘</fire> to not use trait <fire>11 error(s)</fire>
         <green>✔</green> to have method <promote>__construct</promote>

        <violation> ERROR </violation> Controllers architecture rules in StructuraPhp\Structura\Tests\Feature\TestController
        3 classes from
         - dirs
        Should
         <green>✔</green> to be classes
         <green>✔</green> to use declare <promote>strict_types=1</promote>
         <fire>✘</fire> to not use trait <fire>1 error(s)</fire>
         <green>✔</green> to have suffix <promote>Controller</promote>
         <green>✔</green> to extend <promote>StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase</promote>
         <fire>✘</fire> to have method <promote>__construct</promote> <fire>2 error(s)</fire>
         <fire>✘</fire> depends only on these namespaces <promote>StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory, StructuraPhp\Structura\Tests\Fixture\Http\Controller\RoleController, StructuraPhp\Structura\Tests\Fixture\Contract\ShouldQueueInterface, [1+]</promote> <fire>2 error(s)</fire>

        <pass> PASS </pass> Exceptions architecture rules in StructuraPhp\Structura\Tests\Feature\TestException
        2 classes from
         - dirs
        Should
         <green>✔</green> to extend <promote>InvalidArgumentException</promote>
           | to extend <promote>Exception</promote>
           | to extend <promote>DomainException</promote>
             & to extend <promote>BadMethodCallException</promote>

        <pass> PASS </pass> Asserts architecture rules in StructuraPhp\Structura\Tests\Feature\TestVoid
        94 classes from
         - dirs
        That
        Should

        EOF;

        self::assertSame($expected, implode(PHP_EOL, $result->prints));
    }
}

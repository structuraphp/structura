<?php

declare(strict_types=1);

namespace Structura\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Structura\Configs\StructuraConfig;
use Structura\Services\AnalyseService;

#[CoversClass(AnalyseService::class)]
final class AnalyseServiceTest extends TestCase
{
    public function testAnalyseService(): void
    {
        $service = new AnalyseService(
            StructuraConfig::make()
                ->archiRootNamespace(
                    'Structura\Tests\Feature',
                    'tests/Feature',
                ),
        );

        $result = $service->analyse();

        self::assertSame(4, $result->countViolation);
        self::assertSame(12, $result->countPass);

        $expected = <<<'EOF'
        <violation> ERROR </violation> Asserts architecture rules in Structura\Tests\Feature\TestAssert
        24 classes from
         - dirs
        That
         - to implement <promote>Structura\Contracts\ExprInterface</promote>
        Should
         <green>✔</green> to be classes
         <fire>✘</fire> to not depends on these namespaces <promote>Structura\ValueObjects\ClassDescription</promote> <fire>23 error(s)</fire>
         <green>✔</green> to have method <promote>__toString</promote>
         <green>✔</green> to use declare <promote>strict_types=1</promote>
         <green>✔</green> to have prefix <promote>To</promote>
         <green>✔</green> to extend nothing
         <fire>✘</fire> to not use trait <fire>3 error(s)</fire>
         <green>✔</green> to have method <promote>__construct</promote>

        <violation> ERROR </violation> Controllers architecture rules in Structura\Tests\Feature\TestController
        3 classes from
         - dirs
        Should
         <green>✔</green> to be classes
         <green>✔</green> to use declare <promote>strict_types=1</promote>
         <fire>✘</fire> to not use trait <fire>1 error(s)</fire>
         <green>✔</green> to have suffix <promote>Controller</promote>
         <green>✔</green> to extend <promote>Structura\Tests\Fixture\Http\ControllerBase</promote>
         <fire>✘</fire> to have method <promote>__construct</promote> <fire>2 error(s)</fire>
         <green>✔</green> depends only on these namespaces <promote>Structura\Tests\Fixture\Concerns\HasFactory, Structura\Tests\Fixture\Http\Controller\RoleController, Structura\Tests\Fixture\Contract\ShouldQueueInterface, [2+]</promote>

        <pass> PASS </pass> Exceptions architecture rules in Structura\Tests\Feature\TestException
        2 classes from
         - dirs
        Should
         <green>✔</green> to extend <promote>InvalidArgumentException</promote>
           | to extend <promote>Exception</promote>
           | to extend <promote>DomainException</promote>
             & to extend <promote>BadMethodCallException</promote>

        <pass> PASS </pass> Asserts architecture rules in Structura\Tests\Feature\TestVoid
        65 classes from
         - dirs
        That
        Should

        EOF;

        self::assertSame($expected, implode(PHP_EOL, $result->prints));
    }
}

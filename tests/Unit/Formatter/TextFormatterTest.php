<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter;

use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\Formatter\TextFormatter;
use StructuraPhp\Structura\Services\AnalyseService;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @coversNothing
 */
class TextFormatterTest extends TestCase
{
    public function testFormat(): void
    {
        $service = new AnalyseService(
            StructuraConfig::make()
                ->archiRootNamespace(
                    'StructuraPhp\Structura\Tests\Feature',
                    'tests/Feature',
                ),
        );

        $result = $service->analyse();
        $text = new TextFormatter();

        $buffer = new BufferedOutput();

        $out = $text->formatErrors($result, $buffer);
        self::assertSame(1, $out);

        $expected = <<<'EOF'
         ERROR  Asserts architecture rules in StructuraPhp\Structura\Tests\Feature\TestAssert
        40 classes from
         - dirs
        That
         - to implement StructuraPhp\Structura\Contracts\ExprInterface
        Should
         ✔ to be classes
         ✘ to not depends on these namespaces StructuraPhp\Structura\ValueObjects\ClassDescription 34 error(s)
         ✔ to have method __toString
         ✔ to use declare strict_types=1
         ✔ to have prefix To 1 warning(s)
         ✔ to extend nothing
         ✘ to not use trait 7 error(s)
         ✔ to have method __construct

         ERROR  Controllers architecture rules in StructuraPhp\Structura\Tests\Feature\TestController
        3 classes from
         - dirs
        Should
         ✔ to be classes
         ✔ to use declare strict_types=1
         ✘ to not use trait 1 error(s)
         ✔ to have suffix Controller
         ✔ to extend StructuraPhp\Structura\Tests\Fixture\Http\ControllerBase
         ✘ to have method __construct 2 error(s)
         ✘ depends only on these namespaces StructuraPhp\Structura\Tests\Fixture\Concerns\HasFactory, StructuraPhp\Structura\Tests\Fixture\Http\Controller\RoleController, StructuraPhp\Structura\Tests\Fixture\Contract\ShouldQueueInterface, [1+] 2 error(s)

         PASS  Exceptions architecture rules in StructuraPhp\Structura\Tests\Feature\TestException
        Class from
         - raw value
        Should
         ✔ to extend InvalidArgumentException
           | to extend Exception
           | to extend DomainException
             & to extend BadMethodCallException

         PASS  Asserts architecture rules in StructuraPhp\Structura\Tests\Feature\TestVoid
        109 classes from
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

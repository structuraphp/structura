<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter\Progress;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Formatter\Error\ErrorTextFormatter;
use StructuraPhp\Structura\Formatter\Progress\ProgressTextFormatter;
use StructuraPhp\Structura\Tests\DataProvider\FormatterDataProvider;
use StructuraPhp\Structura\Tests\Helper\OutputFormatter;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(ErrorTextFormatter::class)]
class ProgressTextFormatterTest extends TestCase
{
    #[DataProviderExternal(FormatterDataProvider::class, 'getAnalyseValueObject')]
    public function testOutput(AnalyseValueObject $except): void
    {
        $text = new ProgressTextFormatter();

        $buffer = new BufferedOutput(formatter: new OutputFormatter());
        $buffer->setDecorated(true);

        $text->progressStart($buffer, 1);
        $text->progressAdvance($buffer, $except);
        $text->progressFinish($buffer);

        $expected = <<<'EOF'
        <violation> ERROR </violation> Asserts architecture rules in TestAssert
        1 classe(s) from
         - raw value
        That
         - to be classes
        Should
         <green>✔</green> to extend <promote>y</promote>
         <green>✔</green> to be readonly <warning>1 warning(s)</warning>
         <fire>✘</fire> to be final <fire>1 error(s)</fire>

        EOF;

        $expected = explode(PHP_EOL, $expected);

        $fetch = explode(PHP_EOL, $buffer->fetch());

        foreach ($expected as $key => $line) {
            self::assertSame($line, $fetch[$key], sprintf('Error line %d', $key));
        }
    }
}

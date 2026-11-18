<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter\Progress;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Formatter\Progress\ProgressTextFormatter;
use StructuraPhp\Structura\Tests\DataProvider\FormatterDataProvider;
use StructuraPhp\Structura\Tests\Helper\OutputFormatter;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(ProgressTextFormatter::class)]
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
         <yellow>❗</yellow> to be readonly <yellow>1 warning(s)</yellow>
         <fire>✘</fire> to be final <fire>1 error(s)</fire>
         <orange>◎</orange> error notice

        EOF;

        $expected = explode(PHP_EOL, $expected);

        $fetch = explode(PHP_EOL, $buffer->fetch());

        foreach ($expected as $key => $line) {
            self::assertSame($line, $fetch[$key], sprintf('Error line %d', $key));
        }
    }

    public function testPassLabelWhenNoFailure(): void
    {
        $buffer = new BufferedOutput(formatter: new OutputFormatter());
        $buffer->setDecorated(true);

        (new ProgressTextFormatter())->progressAdvance(
            $buffer,
            new AnalyseValueObject(
                timeStart: 0,
                countPass: 0,
                countViolation: 0,
                countWarning: 0,
                countNotice: 0,
                analyseTestValueObjects: [
                    new AnalyseTestValueObject(
                        source: new SourceTestValueObject(
                            testClassname: 'TestAssert',
                            testDox: 'Asserts architecture rules',
                            methodName: '',
                            line: 0,
                            pathname: '',
                        ),
                        ruleValueObjects: [],
                        assertValueObject: new AssertValueObject(),
                    ),
                ],
            ),
        );

        self::assertStringStartsWith(
            '<pass> PASS </pass> Asserts architecture rules in TestAssert' . PHP_EOL,
            $buffer->fetch(),
        );
    }

    public function testProgressFinishWritesEmptyLine(): void
    {
        $buffer = new BufferedOutput();

        (new ProgressTextFormatter())->progressFinish($buffer);

        self::assertSame(PHP_EOL, $buffer->fetch());
    }
}

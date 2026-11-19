<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\Formatter\Error\ErrorTextFormatter;
use StructuraPhp\Structura\Tests\DataProvider\FormatterDataProvider;
use StructuraPhp\Structura\Tests\Helper\OutputFormatter;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(ErrorTextFormatter::class)]
class ErrorTextFormatterTest extends TestCase
{
    #[DataProviderExternal(FormatterDataProvider::class, 'getAnalyseValueObject')]
    public function testOutput(AnalyseValueObject $except): void
    {
        $text = new ErrorTextFormatter();

        $buffer = new BufferedOutput(formatter: new OutputFormatter());
        $buffer->setDecorated(true);

        $output = $text->formatErrors($except, $buffer);

        $expected = <<<'EOF'
        <violation> ERROR LIST </violation>

        Resource <promote>x</promote> must be a final class
        example.php:1
        
        <warning> WARNING LIST </warning>

        <promote>ToBeReadonly</promote> exception for <promote>x</promote> is no longer applicable
        
        <notice> NOTICE LIST </notice>

        error notice

        Tests:    <green>10 passed</green>, <fire>10 failed</fire>, <yellow>1 warning</yellow>, <orange>1 notice</orange> (21 assertion)
        EOF;

        $expected = explode(PHP_EOL, $expected);

        $fetch = explode(PHP_EOL, $buffer->fetch());

        self::assertSame(ErrorFormatterInterface::ERROR, $output);
        foreach ($expected as $key => $line) {
            self::assertSame($line, $fetch[$key], sprintf('Error line %d', $key));
        }
    }

    public function testOutputSkipsZeroCountsAndPrintsDurationAndMemory(): void
    {
        $buffer = new BufferedOutput(formatter: new OutputFormatter());
        $buffer->setDecorated(true);

        $peakBefore = memory_get_peak_usage(true) / 1024 / 1024;

        (new ErrorTextFormatter())->formatErrors(
            FormatterDataProvider::makeOneViolation(timeStart: microtime(true) - 65),
            $buffer,
        );

        $peakAfter = memory_get_peak_usage(true) / 1024 / 1024;
        $fetch = $buffer->fetch();

        self::assertStringContainsString(
            'Tests:    <fire>1 failed</fire> (1 assertion)' . PHP_EOL,
            $fetch,
        );

        // a one second window: Infection runs this test on loaded CI runners
        self::assertSame(
            1,
            preg_match('/^Duration: 01:05\.\d{3}, Memory: ([\d.]+) MB$/m', $fetch, $matches),
        );

        // the peak only grows: the printed value is bounded by the peaks around the call
        self::assertGreaterThanOrEqual($peakBefore, (float) $matches[1]);
        self::assertLessThanOrEqual($peakAfter, (float) $matches[1]);
    }

    public function testSummaryShowsToleratedCount(): void
    {
        $buffer = new BufferedOutput(formatter: new OutputFormatter());
        $buffer->setDecorated(true);

        (new ErrorTextFormatter())->formatErrors(FormatterDataProvider::makeOneViolation()->withBaseline(5, []), $buffer);

        self::assertStringContainsString(
            'Tests:    <fire>1 failed</fire>, <yellow>5 tolerated by baseline</yellow> (1 assertion)',
            $buffer->fetch(),
        );
    }

    public function testSummaryHidesZeroToleratedCount(): void
    {
        $buffer = new BufferedOutput(formatter: new OutputFormatter());
        $buffer->setDecorated(true);

        (new ErrorTextFormatter())->formatErrors(FormatterDataProvider::makeOneViolation()->withBaseline(0, []), $buffer);

        self::assertStringNotContainsString('tolerated', $buffer->fetch());
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter\Progress;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Formatter\Progress\ProgressNoneFormatter;
use StructuraPhp\Structura\Tests\DataProvider\FormatterDataProvider;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(ProgressNoneFormatter::class)]
class ProgressNoneFormatterTest extends TestCase
{
    #[DataProviderExternal(FormatterDataProvider::class, 'getAnalyseValueObject')]
    public function testOutputIsEmpty(AnalyseValueObject $except): void
    {
        $formatter = new ProgressNoneFormatter();

        $buffer = new BufferedOutput();
        $buffer->setDecorated(true);

        $formatter->progressStart($buffer, 1);
        $formatter->progressAdvance($buffer, $except);
        $formatter->progressFinish($buffer);

        self::assertSame('', $buffer->fetch());
    }

    #[DataProviderExternal(FormatterDataProvider::class, 'getAnalyseValueObject')]
    public function testStopOnOutputIsEmpty(AnalyseValueObject $except): void
    {
        $formatter = new ProgressNoneFormatter();

        $buffer = new BufferedOutput();
        $buffer->setDecorated(true);

        $formatter->progressStart($buffer, 1);
        $formatter->progressAdvance($buffer, $except);
        $formatter->progressStopOn($buffer);

        self::assertSame('', $buffer->fetch());
    }
}

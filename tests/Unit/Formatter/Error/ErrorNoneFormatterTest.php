<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\Formatter\Error\ErrorNoneFormatter;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(ErrorNoneFormatter::class)]
class ErrorNoneFormatterTest extends TestCase
{
    public function testOutputIsEmptyAndReturnsSuccessWhenNoViolations(): void
    {
        $formatter = new ErrorNoneFormatter();

        $buffer = new BufferedOutput();
        $buffer->setDecorated(true);

        $analyseValueObject = new AnalyseValueObject(
            timeStart: 10,
            countPass: 5,
            countViolation: 0,
            countWarning: 0,
            countNotice: 0,
            analyseTestValueObjects: [],
        );

        $result = $formatter->formatErrors($analyseValueObject, $buffer);

        self::assertSame('', $buffer->fetch());
        self::assertSame(ErrorFormatterInterface::SUCCESS, $result);
    }

    public function testOutputIsEmptyAndReturnsErrorWhenViolationsExist(): void
    {
        $formatter = new ErrorNoneFormatter();

        $buffer = new BufferedOutput();
        $buffer->setDecorated(true);

        $analyseValueObject = new AnalyseValueObject(
            timeStart: 10,
            countPass: 5,
            countViolation: 3,
            countWarning: 1,
            countNotice: 1,
            analyseTestValueObjects: [],
        );

        $result = $formatter->formatErrors($analyseValueObject, $buffer);

        self::assertSame('', $buffer->fetch());
        self::assertSame(ErrorFormatterInterface::ERROR, $result);
    }
}

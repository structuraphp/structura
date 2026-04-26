<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\Formatter\Error\ErrorPrettyJsonFormatter;
use StructuraPhp\Structura\Tests\DataProvider\FormatterDataProvider;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(ErrorPrettyJsonFormatter::class)]
class ErrorPrettyJsonFormatterTest extends TestCase
{
    #[DataProviderExternal(FormatterDataProvider::class, 'getAnalyseValueObject')]
    public function testOutputIsValidJsonAndReturnsError(AnalyseValueObject $except): void
    {
        $formatter = new ErrorPrettyJsonFormatter();

        $buffer = new BufferedOutput();

        $result = $formatter->formatErrors($except, $buffer);

        $json = $buffer->fetch();

        self::assertSame(ErrorFormatterInterface::ERROR, $result);
        self::assertJson($json);

        $decoded = json_decode($json, true);

        self::assertIsArray($decoded);
        self::assertArrayHasKey('assertion_total', $decoded);
        self::assertArrayHasKey('assertion_detail', $decoded);
        self::assertArrayHasKey('duration_ms', $decoded);
        self::assertIsArray($decoded['assertion_detail']);

        self::assertSame(21, $decoded['assertion_total']);
        self::assertSame(10, $decoded['assertion_detail']['pass']);
        self::assertSame(10, $decoded['assertion_detail']['violations']);
        self::assertSame(1, $decoded['assertion_detail']['warnings']);
        self::assertSame(1, $decoded['assertion_detail']['notices']);
        self::assertGreaterThanOrEqual(0, $decoded['duration_ms']);

        self::assertArrayHasKey('errors', $decoded);
        self::assertIsArray($decoded['errors']);
        self::assertCount(1, $decoded['errors']);
        self::assertIsArray($decoded['errors'][0]);
        self::assertSame('to be final', $decoded['errors'][0]['rule']);
        self::assertSame('example.php', $decoded['errors'][0]['file']);
        self::assertSame(1, $decoded['errors'][0]['line']);

        self::assertArrayHasKey('warnings', $decoded);
        self::assertIsArray($decoded['warnings']);
        self::assertCount(1, $decoded['warnings']);
        self::assertIsArray($decoded['warnings'][0]);
        self::assertSame('Foo', $decoded['warnings'][0]['rule']);

        self::assertArrayHasKey('notices', $decoded);
        self::assertIsArray($decoded['notices']);
        self::assertCount(1, $decoded['notices']);
        self::assertIsArray($decoded['notices'][0]);
        self::assertSame('to be final', $decoded['notices'][0]['rule']);
        self::assertSame('error notice', $decoded['notices'][0]['message']);
    }

    public function testOutputReturnsSuccessWhenNoViolations(): void
    {
        $formatter = new ErrorPrettyJsonFormatter();

        $buffer = new BufferedOutput();

        $analyseValueObject = new AnalyseValueObject(
            timeStart: 10,
            countPass: 5,
            countViolation: 0,
            countWarning: 0,
            countNotice: 0,
            violationsByTests: [],
            warningsByTests: [],
            noticeByTests: [],
            analyseTestValueObjects: [],
        );

        $result = $formatter->formatErrors($analyseValueObject, $buffer);

        $json = $buffer->fetch();

        self::assertSame(ErrorFormatterInterface::SUCCESS, $result);
        self::assertJson($json);

        $decoded = json_decode($json, true);

        self::assertIsArray($decoded);
        self::assertArrayHasKey('assertion_total', $decoded);
        self::assertArrayHasKey('assertion_detail', $decoded);
        self::assertArrayHasKey('duration_ms', $decoded);
        self::assertIsArray($decoded['assertion_detail']);

        self::assertSame(5, $decoded['assertion_total']);
        self::assertSame(0, $decoded['assertion_detail']['violations']);
        self::assertGreaterThanOrEqual(0, $decoded['duration_ms']);
        self::assertArrayNotHasKey('errors', $decoded);
        self::assertArrayNotHasKey('warnings', $decoded);
        self::assertArrayNotHasKey('notices', $decoded);
    }
}

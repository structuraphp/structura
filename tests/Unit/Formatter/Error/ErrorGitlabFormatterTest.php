<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Formatter\Error;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\Formatter\Error\ErrorGitlabFormatter;
use StructuraPhp\Structura\Tests\DataProvider\FormatterDataProvider;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\BufferedOutput;

#[CoversClass(ErrorGitlabFormatter::class)]
class ErrorGitlabFormatterTest extends TestCase
{
    #[DataProviderExternal(FormatterDataProvider::class, 'getAnalyseValueObject')]
    public function testOutputContainsViolations(AnalyseValueObject $analyseValueObject): void
    {
        $formatter = new ErrorGitlabFormatter();
        $buffer = new BufferedOutput();

        $result = $formatter->formatErrors($analyseValueObject, $buffer);

        self::assertSame(ErrorFormatterInterface::ERROR, $result);

        $json = $buffer->fetch();
        $decoded = json_decode($json, true);

        self::assertIsArray($decoded);
        self::assertNotEmpty($decoded);

        /** @var array<string, mixed> $issue */
        $issue = $decoded[0];
        self::assertArrayHasKey('description', $issue);
        self::assertArrayHasKey('fingerprint', $issue);
        self::assertArrayHasKey('severity', $issue);
        self::assertArrayHasKey('location', $issue);

        self::assertSame('major', $issue['severity']);

        /** @var array{path: string, lines: array{begin: int}} $location */
        $location = $issue['location'];
        self::assertSame('example.php', $location['path']);
        self::assertSame(1, $location['lines']['begin']);

        $expectedFingerprint = hash(
            'sha256',
            'example.php1' . strip_tags('Resource <promote>x</promote> must be a final class'),
        );
        self::assertSame($expectedFingerprint, $issue['fingerprint']);
    }

    public function testOutputEmptyWhenNoViolations(): void
    {
        $formatter = new ErrorGitlabFormatter();
        $buffer = new BufferedOutput();

        $analyseValueObject = new AnalyseValueObject(
            timeStart: 0,
            countPass: 1,
            countViolation: 0,
            countWarning: 0,
            countNotice: 0,
            violationsByTests: [[]],
            warningsByTests: [[]],
            noticeByTests: [[]],
            analyseTestValueObjects: [],
        );

        $result = $formatter->formatErrors($analyseValueObject, $buffer);

        self::assertSame(ErrorFormatterInterface::SUCCESS, $result);

        $json = $buffer->fetch();
        $decoded = json_decode($json, true);

        self::assertIsArray($decoded);
        self::assertEmpty($decoded);
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter\Error;

use DateTime;
use Exception;
use StructuraPhp\Structura\Console\Enums\StyleCustom;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @phpstan-import-type ViolationsByTest from AnalyseValueObject
 * @phpstan-import-type WarningByTest from AnalyseValueObject
 */
final class ErrorTextFormatter implements ErrorFormatterInterface
{
    /** @var array<int,string> */
    private array $prints = [];

    public function formatErrors(
        AnalyseValueObject $analyseValueObject,
        OutputInterface $output,
    ): int {
        $violations = array_merge(...$analyseValueObject->violationsByTests);
        $warnings = array_merge(...$analyseValueObject->warningsByTests);
        $notices = array_merge(...$analyseValueObject->noticeByTests);

        if ($violations !== []) {
            $this->failedOutput($violations);
        }

        if ($warnings !== []) {
            $this->warningOutput($warnings);
        }

        if ($notices !== []) {
            $this->noticeOutput($notices);
        }

        $this->assertionsResumeOutput($analyseValueObject);
        $this->durationAndTimeOutput($analyseValueObject->timeStart);

        foreach ($this->prints as $print) {
            $this->styleCustom($output)->writeln($print);
        }

        return $violations === []
            ? self::SUCCESS
            : self::ERROR;
    }

    private function styleCustom(OutputInterface $output): OutputInterface
    {
        foreach (StyleCustom::cases() as $style) {
            $output
                ->getFormatter()
                ->setStyle(
                    $style->value,
                    $style->getOutputFormatterStyle(),
                );
        }

        return $output;
    }

    /**
     * @param ViolationsByTest $violationsByTests
     */
    private function failedOutput(array $violationsByTests): void
    {
        $this->prints[] = '<violation> ERROR LIST </violation>';
        $this->prints[] = '';

        foreach ($violationsByTests as $violationsByTest) {
            foreach ($violationsByTest as $violation) {
                $this->prints[] = $violation->messageViolation;
                $this->prints[] = \sprintf(
                    '%s:%d',
                    $violation->pathname,
                    $violation->line,
                );
                $this->prints[] = '';
            }
        }
    }

    /**
     * @param array<string, string> $noticesByTests
     */
    private function noticeOutput(array $noticesByTests): void
    {
        $this->prints[] = '<notice> NOTICE LIST </notice>';
        $this->prints[] = '';

        foreach ($noticesByTests as $noticeByTests) {
            $this->prints[] = $noticeByTests;
            $this->prints[] = '';
        }
    }

    /**
     * @param WarningByTest $warningsByTests
     */
    private function warningOutput(array $warningsByTests): void
    {
        $this->prints[] = '<warning> WARNING LIST </warning>';
        $this->prints[] = '';

        foreach ($warningsByTests as $warningsByTest) {
            foreach ($warningsByTest as $warning) {
                $this->prints[] = $warning;
                $this->prints[] = '';
            }
        }
    }

    private function assertionsResumeOutput(AnalyseValueObject $analyseDto): void
    {
        $data = [
            '<green>%d passed</green>' => $analyseDto->countPass,
            '<fire>%d failed</fire>' => $analyseDto->countViolation,
            '<yellow>%d warning</yellow>' => $analyseDto->countWarning,
            '<orange>%d notice</orange>' => $analyseDto->countNotice,
        ];

        $data = array_filter($data, fn (int $value): bool => $value > 0);

        $print = sprintf(
            '%-9s ' . implode(', ', array_keys($data)),
            'Tests:',
            ...array_values($data),
        );
        $print .= sprintf(
            ' (%d assertion)',
            $analyseDto->countPass + $analyseDto->countViolation + $analyseDto->countWarning,
        );
        $this->prints[] = $print;
    }

    private function durationAndTimeOutput(float $time_start): void
    {
        $timeEnd = microtime(true);
        $time = $timeEnd - $time_start;
        $now = $this->tryDuration($time);

        $this->prints[] = \sprintf(
            'Duration: %s, Memory: %s MB',
            substr($now->format('i:s.u'), 0, -3),
            (string) memory_get_peak_usage(true) / 1024 / 1024,
        );
    }

    private function tryDuration(float $time): DateTime
    {
        $now = DateTime::createFromFormat(
            'U.u',
            number_format($time, 3, '.', ''),
        );

        return $now === false
            ? throw new Exception()
            : $now;
    }
}

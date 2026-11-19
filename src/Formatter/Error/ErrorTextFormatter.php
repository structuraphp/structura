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
 * @phpstan-import-type NoticesByTest from AnalyseValueObject
 */
final class ErrorTextFormatter implements ErrorFormatterInterface
{
    /** @var array<int,string> */
    private array $prints = [];

    public function formatErrors(
        AnalyseValueObject $analyseValueObject,
        OutputInterface $output,
    ): int {
        $violations = $analyseValueObject->getViolations();
        $warnings = $analyseValueObject->getWarnings();
        $notices = $analyseValueObject->getNotices();

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

        // styles registered once for all the lines, not once per line
        $this->styleCustom($output)->writeln($this->prints);

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
     * @param ViolationsByTest $violations
     */
    private function failedOutput(array $violations): void
    {
        $this->prints[] = '<violation> ERROR LIST </violation>';
        $this->prints[] = '';

        foreach ($violations as $violationsByTest) {
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
     * @param NoticesByTest $notices
     */
    private function noticeOutput(array $notices): void
    {
        $this->prints[] = '<notice> NOTICE LIST </notice>';
        $this->prints[] = '';

        foreach ($notices as $notice) {
            $this->prints[] = $notice;
            $this->prints[] = '';
        }
    }

    /**
     * @param WarningByTest $warnings
     */
    private function warningOutput(array $warnings): void
    {
        $this->prints[] = '<warning> WARNING LIST </warning>';
        $this->prints[] = '';

        foreach ($warnings as $warningsByTest) {
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
            '<yellow>%d tolerated by baseline</yellow>' => (int) $analyseDto->countTolerated,
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

    private function durationAndTimeOutput(float $timeStart): void
    {
        $duration = DateTime::createFromFormat('U.u', \sprintf('%.6F', microtime(true) - $timeStart));
        if ($duration === false) {
            throw new Exception();
        }

        $this->prints[] = \sprintf(
            'Duration: %s, Memory: %s MB',
            $duration->format('i:s.v'),
            memory_get_peak_usage(true) / 1024 / 1024,
        );
    }
}

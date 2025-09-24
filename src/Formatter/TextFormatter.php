<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter;

use DateTime;
use Exception;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Builder\AssertBuilder;
use StructuraPhp\Structura\Console\Enums\StyleCustom;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * @phpstan-import-type ViolationsByTest from AnalyseValueObject
 */
final class TextFormatter implements ErrorFormatterInterface
{
    /** @var array<int,string> */
    private array $prints = [];

    public function formatErrors(AnalyseValueObject $analyseValueObject, Output $output): int
    {
        foreach ($analyseValueObject->analyseTestValueObjects as $data) {
            $this->prints[] = \sprintf(
                '%s %s in %s',
                $data->assertBuilder->countAssertsFailure() === 0
                    ? '<pass> PASS </pass>'
                    : '<violation> ERROR </violation>',
                $data->textDox,
                $data->classname,
            );
            $this->fromOutput($data->ruleValueObject->finder);
            $this->thatOutput($data->ruleValueObject->that);
            $this->shouldOutput($data->assertBuilder);
            $this->prints[] = '';
        }

        $this->prints[] = '';
        $violations = array_merge(...$analyseValueObject->violationsByTests);

        $this->failedOutput($violations);
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

    private function assertionsResumeOutput(AnalyseValueObject $analyseDto): void
    {
        $data = [
            '<green>%d passed</green>' => $analyseDto->countPass,
            '<fire>%d failed</fire>' => $analyseDto->countViolation,
            '<warning>%d warning</warning>' => $analyseDto->countWarning,
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
            'Duration: %s, Memory: %d MB',
            substr($now->format('i:s.u'), 0, -3),
            memory_get_peak_usage(true) / 1024 / 1024,
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

    private function fromOutput(?Finder $finder): void
    {
        if ($finder instanceof Finder) {
            $this->prints[] = $finder->count() . ' classes from';
            $this->prints[] = ' - dirs';
        } else {
            $this->prints[] = 'Class from';
            $this->prints[] = ' - raw value';
        }
    }

    private function thatOutput(?AbstractExpr $builder): void
    {
        if (!$builder instanceof AbstractExpr) {
            return;
        }

        $this->prints[] = 'That';

        foreach ($builder as $expr) {
            $this->prints[] = \sprintf(' - %s', $expr);
        }
    }

    private function shouldOutput(AssertBuilder $assertBuilder): void
    {
        $this->prints[] = 'Should';

        foreach ($assertBuilder->getPass() as $message => $isPass) {
            if ($isPass === 0) {
                $this->prints[] = \sprintf(
                    ' <fire>✘</fire> %s <fire>%d error(s)</fire>',
                    $message,
                    $assertBuilder->countViolation($message),
                );
            } else {
                $countWarning = $assertBuilder->countWarning($message);
                $warning = $countWarning !== 0
                    ? sprintf(' <warning>%d warning(s)</warning>', $countWarning)
                    : '';

                $this->prints[] = \sprintf(
                    ' <green>✔</green> %s%s',
                    $message,
                    $warning,
                );
            }
        }
    }
}

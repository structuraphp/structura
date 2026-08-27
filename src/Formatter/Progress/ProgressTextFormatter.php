<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter\Progress;

use StructuraPhp\Structura\Console\Enums\StyleCustom;
use StructuraPhp\Structura\Contracts\ProgressFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use StructuraPhp\Structura\ValueObjects\RuleDescriptionValueObject;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @phpstan-import-type ViolationsByTest from AnalyseValueObject
 */
final class ProgressTextFormatter implements ProgressFormatterInterface
{
    /** @var array<int,string> */
    private array $prints = [];

    public function progressStart(OutputInterface $output, int $max): void
    {
        // Nothing
    }

    public function progressAdvance(OutputInterface $output, AnalyseValueObject $analyseValueObject): void
    {
        foreach ($analyseValueObject->analyseTestValueObjects as $data) {
            $this->headOutput($data);

            foreach ($data->ruleDescriptions as $ruleDescription) {
                $this->fromOutput($ruleDescription);
                $this->thatOutput($ruleDescription);
            }

            $this->shouldOutput($data->assertValueObject);
            $this->prints[] = '';

            foreach ($this->prints as $print) {
                $this->styleCustom($output)->writeln($print);
            }

            $this->prints = [];
        }
    }

    public function progressFinish(OutputInterface $output): void
    {
        $output->writeln('');
    }

    public function progressStopOn(OutputInterface $output): void
    {
        $output->writeln('');
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

    private function headOutput(AnalyseTestValueObject $data): void
    {
        $label = '<pass> PASS </pass>';
        if ($data->assertValueObject->countAssertsFailure() > 0) {
            $label = '<violation> ERROR </violation>';
        } elseif ($data->assertValueObject->countAssertsWarning() > 0) {
            $label = '<warning> WARNING </warning>';
        } elseif ($data->assertValueObject->countAssertsNotices() > 0) {
            $label = '<notice> NOTICE </notice>';
        }

        $this->prints[] = \sprintf(
            '%s %s in %s',
            $label,
            $data->source->textDox,
            $data->source->testClassname,
        );
    }

    private function fromOutput(RuleDescriptionValueObject $ruleDescription): void
    {
        $this->prints[] = $ruleDescription->sourceCount . ' classe(s) from';
        $this->prints[] = $ruleDescription->fromFinder ? ' - dirs' : ' - raw value';
    }

    private function thatOutput(RuleDescriptionValueObject $ruleDescription): void
    {
        if ($ruleDescription->thatExpressions === null) {
            return;
        }

        $this->prints[] = 'That';

        foreach ($ruleDescription->thatExpressions as $expr) {
            $this->prints[] = \sprintf(' - %s', $expr);
        }
    }

    private function shouldOutput(AssertValueObject $assertValueObject): void
    {
        $this->prints[] = 'Should';

        foreach ($assertValueObject->pass as $message => $isPass) {
            if ($isPass === 0) {
                $this->prints[] = \sprintf(
                    ' <fire>✘</fire> %s <fire>%d error(s)</fire>',
                    $message,
                    $assertValueObject->countViolation($message),
                );
            } elseif ($isPass === 1) {
                $this->prints[] = \sprintf(
                    ' <green>✔</green> %s',
                    $message,
                );
            } elseif ($isPass === 2) {
                $this->prints[] = \sprintf(
                    ' <yellow>❗</yellow> %s <yellow>%d warning(s)</yellow>',
                    $message,
                    $assertValueObject->countWarning($message),
                );
            } elseif ($isPass === 3) {
                $this->prints[] = sprintf(
                    ' <orange>◎</orange> %s',
                    $assertValueObject->notices[$message] ?? '',
                );
            }
        }
    }
}

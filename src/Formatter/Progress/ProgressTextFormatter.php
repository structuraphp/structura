<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter\Progress;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Console\Enums\StyleCustom;
use StructuraPhp\Structura\Contracts\ProgressFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

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
            $this->prints[] = \sprintf(
                '%s %s in %s',
                $data->assertValueObject->countAssertsFailure() === 0
                    ? '<pass> PASS </pass>'
                    : '<violation> ERROR </violation>',
                $data->textDox,
                $data->classname,
            );
            $this->fromOutput($data->ruleValueObject->finder, $data->ruleValueObject->raws);
            $this->thatOutput($data->ruleValueObject->that);
            $this->shouldOutput($data->assertValueObject);
            $this->prints[] = '';

            foreach ($this->prints as $print) {
                $this->styleCustom($output)->writeln($print);
            }

            unset($this->prints);
        }

        $output->writeln('');
    }

    public function progressFinish(OutputInterface $output): void
    {
        // Nothing
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
     * @param array<string,string> $raws
     */
    private function fromOutput(?Finder $finder, array $raws = []): void
    {
        if ($finder instanceof Finder) {
            $this->prints[] = $finder->count() . ' classe(s) from';
            $this->prints[] = ' - dirs';
        } else {
            $this->prints[] = count($raws) . ' classe(s) from';
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
            } else {
                $countWarning = $assertValueObject->countWarning($message);
                $warning = $countWarning !== 0
                    ? sprintf(' <yellow>%d warning(s)</yellow>', $countWarning)
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

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter\Progress;

use RuntimeException;
use StructuraPhp\Structura\Contracts\ProgressFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;

class ProgressBarFormatter implements ProgressFormatterInterface
{
    public function progressStart(OutputInterface $output, int $max): void
    {
        $output instanceof OutputStyle
            ? $output->progressStart($max)
            : throw new RuntimeException();
    }

    public function progressAdvance(OutputInterface $output, AnalyseValueObject $analyseValueObject): void
    {
        $output instanceof OutputStyle
            ? $output->progressAdvance()
            : throw new RuntimeException();
    }

    public function progressFinish(OutputInterface $output): void
    {
        $output instanceof OutputStyle
            ? $output->progressFinish()
            : throw new RuntimeException();
    }

    public function progressStopOn(OutputInterface $output): void
    {
        $output instanceof OutputStyle
            ? $output->newLine(2)
            : throw new RuntimeException();
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter\Progress;

use StructuraPhp\Structura\Contracts\ProgressFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\OutputInterface;

final class ProgressNoneFormatter implements ProgressFormatterInterface
{
    public function progressStart(OutputInterface $output, int $max): void {}

    public function progressAdvance(OutputInterface $output, AnalyseValueObject $analyseValueObject): void {}

    public function progressFinish(OutputInterface $output): void {}

    public function progressStopOn(OutputInterface $output): void {}
}

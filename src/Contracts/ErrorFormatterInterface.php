<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\OutputInterface;

interface ErrorFormatterInterface
{
    public const SUCCESS = 0;

    public const ERROR = 1;

    public function formatErrors(
        AnalyseValueObject $analyseValueObject,
        OutputInterface $output,
    ): int;
}

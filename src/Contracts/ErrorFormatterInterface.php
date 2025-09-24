<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\Output;

interface ErrorFormatterInterface
{
    public const SUCCESS = 0;

    public const ERROR = 1;

    public function formatErrors(
        AnalyseValueObject $analyseValueObject,
        Output $output,
    ): int;
}

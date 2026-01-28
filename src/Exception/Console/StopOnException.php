<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Exception\Console;

use RuntimeException;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;

final class StopOnException extends RuntimeException
{
    public function __construct(public readonly AnalyseValueObject $analyseValueObject)
    {
        parent::__construct();
    }
}

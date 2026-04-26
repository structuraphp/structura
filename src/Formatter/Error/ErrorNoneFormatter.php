<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter\Error;

use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\OutputInterface;

final class ErrorNoneFormatter implements ErrorFormatterInterface
{
    public function formatErrors(
        AnalyseValueObject $analyseValueObject,
        OutputInterface $output,
    ): int {
        if ($analyseValueObject->countViolation > 0) {
            return self::ERROR;
        }

        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter;

use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use Symfony\Component\Console\Output\Output;

/**
 * @phpstan-import-type ViolationsByTest from AnalyseValueObject
 */
class GithubFormatter implements ErrorFormatterInterface
{
    public function formatErrors(AnalyseValueObject $analyseValueObject, Output $output): int
    {
        /** @var ViolationsByTest $violationsByTests */
        $violationsByTests = array_merge(...$analyseValueObject->violationsByTests);

        foreach ($violationsByTests as $violationsByTest) {
            foreach ($violationsByTest as $violation) {
                $metas = [
                    'file' => $violation->pathname,
                    'line' => $violation->line,
                    'col' => 0,
                ];
                array_walk($metas, static function (&$value, string $key): void {
                    $value = sprintf('%s=%s', $key, (string)$value);
                });

                $message = $violation->messageViolation;
                // newlines need to be encoded
                // see https://github.com/actions/starter-workflows/issues/68#issuecomment-581479448
                $message = str_replace("\n", '%0A', $message);

                $line = sprintf('::error %s::%s', implode(',', $metas), $message);

                $output->writeln($line, Output::OUTPUT_RAW);
            }
        }

        return $violationsByTests === []
            ? self::SUCCESS
            : self::ERROR;
    }
}

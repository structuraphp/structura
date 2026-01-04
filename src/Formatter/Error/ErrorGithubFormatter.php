<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter\Error;

use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @phpstan-import-type ViolationsByTest from AnalyseValueObject
 * @phpstan-import-type WarningByTest from AnalyseValueObject
 */
class ErrorGithubFormatter implements ErrorFormatterInterface
{
    public function formatErrors(AnalyseValueObject $analyseValueObject, OutputInterface $output): int
    {
        /** @var ViolationsByTest $violationsByTests */
        $violationsByTests = array_merge(...$analyseValueObject->violationsByTests);

        /** @var WarningByTest $warningsByTests */
        $warningsByTests = array_merge(...$analyseValueObject->warningsByTests);

        /** @var array<int, ViolationValueObject> $violationsByTest */
        foreach ($violationsByTests as $violationsByTest) {
            foreach ($violationsByTest as $violation) {
                $metas = [
                    'file' => $violation->pathname,
                    'line' => $violation->line,
                    'col' => 0,
                ];
                array_walk($metas, static function (&$value, string $key): void {
                    $value = sprintf('%s=%s', $key, (string) $value);
                });

                $message = $this->formatMessage($violation->messageViolation);

                $line = sprintf('::error %s::%s', implode(',', $metas), $message);

                $output->writeln($line, Output::OUTPUT_RAW);
            }
        }

        /** @var array<int, string> $warningsByTest */
        foreach ($warningsByTests as $warningsByTest) {
            foreach ($warningsByTest as $warning) {
                $message = $this->formatMessage($warning);

                $line = sprintf('::warning ::%s', $message);

                $output->writeln($line, Output::OUTPUT_RAW);
            }
        }

        return $violationsByTests === []
            ? self::SUCCESS
            : self::ERROR;
    }

    /**
     * Newlines need to be encoded.
     *
     * @see https://github.com/actions/starter-workflows/issues/68#issuecomment-581479448
     */
    private function formatMessage(string $message): string
    {
        return str_replace("\n", '%0A', $message);
    }
}

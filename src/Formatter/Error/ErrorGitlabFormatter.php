<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter\Error;

use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @see https://docs.gitlab.com/ci/testing/code_quality#code-quality-report-format
 *
 * @phpstan-import-type ViolationsByTest from AnalyseValueObject
 *
 * @phpstan-type GitlabIssue array{
 *     description: string,
 *     fingerprint: string,
 *     severity: string,
 *     location: array{
 *         path: string,
 *         lines: array{begin: int}
 *     }
 * }
 */
class ErrorGitlabFormatter implements ErrorFormatterInterface
{
    public function formatErrors(AnalyseValueObject $analyseValueObject, OutputInterface $output): int
    {
        /** @var ViolationsByTest $violationsByTests */
        $violationsByTests = array_merge(...$analyseValueObject->violationsByTests);

        /** @var list<GitlabIssue> $issues */
        $issues = [];

        /** @var array<int, ViolationValueObject> $violationsByTest */
        foreach ($violationsByTests as $violationsByTest) {
            foreach ($violationsByTest as $violation) {
                $path = $violation->pathname ?? '';
                $line = $violation->line;
                $message = strip_tags($violation->messageViolation);

                $issues[] = [
                    'description' => $message,
                    'fingerprint' => hash('sha256', $path . $line . $message),
                    'severity' => 'major',
                    'location' => [
                        'path' => $path,
                        'lines' => [
                            'begin' => $line,
                        ],
                    ],
                ];
            }
        }

        $output->writeln(
            (string) json_encode($issues, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            OutputInterface::OUTPUT_RAW,
        );

        return $issues === []
            ? self::SUCCESS
            : self::ERROR;
    }
}

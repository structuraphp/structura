<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Formatter\Error;

use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @phpstan-import-type ViolationsByTest from AnalyseValueObject
 * @phpstan-import-type WarningByTest from AnalyseValueObject
 */
final class ErrorJsonFormatter implements ErrorFormatterInterface
{
    public function formatErrors(
        AnalyseValueObject $analyseValueObject,
        OutputInterface $output,
    ): int {
        /** @var ViolationsByTest $violationsByTests */
        $violationsByTests = array_merge(...$analyseValueObject->violationsByTests);

        /** @var WarningByTest $warningsByTests */
        $warningsByTests = array_merge(...$analyseValueObject->warningsByTests);

        /** @var array<string, string> $noticesByTests */
        $noticesByTests = array_merge(...$analyseValueObject->noticeByTests);

        $errors = [];

        /** @var array<int, ViolationValueObject> $violationsByTest */
        foreach ($violationsByTests as $rule => $violationsByTest) {
            foreach ($violationsByTest as $violation) {
                $errors[] = [
                    'rule' => strip_tags($rule),
                    'message' => strip_tags($violation->messageViolation),
                    'file' => $violation->pathname,
                    'line' => $violation->line,
                ];
            }
        }

        $warnings = [];

        /** @var array<int, string> $warningsByTest */
        foreach ($warningsByTests as $rule => $warningsByTest) {
            foreach ($warningsByTest as $warning) {
                $warnings[] = [
                    'rule' => strip_tags($rule),
                    'message' => strip_tags($warning),
                ];
            }
        }

        $notices = [];

        foreach ($noticesByTests as $rule => $notice) {
            $notices[] = [
                'rule' => strip_tags($rule),
                'message' => strip_tags($notice),
            ];
        }

        $json = [
            'assertion_total' => $analyseValueObject->countPass
                + $analyseValueObject->countViolation
                + $analyseValueObject->countWarning,
            'assertion_detail' => [
                'pass' => $analyseValueObject->countPass,
                'violations' => $analyseValueObject->countViolation,
                'warnings' => $analyseValueObject->countWarning,
                'notices' => $analyseValueObject->countNotice,
            ],
            'duration_ms' => (int) round((microtime(true) - $analyseValueObject->timeStart) * 1000),
        ];

        if ($errors !== []) {
            $json['errors'] = $errors;
        }

        if ($warnings !== []) {
            $json['warnings'] = $warnings;
        }

        if ($notices !== []) {
            $json['notices'] = $notices;
        }

        $output->writeln(
            (string) json_encode($json, JSON_UNESCAPED_UNICODE),
            OutputInterface::OUTPUT_RAW,
        );

        return $violationsByTests === []
            ? self::SUCCESS
            : self::ERROR;
    }
}

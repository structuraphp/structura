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
        $violations = $analyseValueObject->getViolations();
        $warnings = $analyseValueObject->getWarnings();
        $notices = $analyseValueObject->getNotices();

        $errors = [];

        /** @var array<int, ViolationValueObject> $violationsByTest */
        foreach ($violations as $rule => $violationsByTest) {
            foreach ($violationsByTest as $violation) {
                $errors[] = [
                    'rule' => strip_tags($rule),
                    'message' => strip_tags($violation->messageViolation),
                    'file' => $violation->pathname,
                    'line' => $violation->line,
                ];
            }
        }

        $warningsJson = [];

        /** @var array<int, string> $warningsByTest */
        foreach ($warnings as $rule => $warningsByTest) {
            foreach ($warningsByTest as $warning) {
                $warningsJson[] = [
                    'rule' => strip_tags($rule),
                    'message' => strip_tags($warning),
                ];
            }
        }

        $noticesJson = [];

        foreach ($notices as $rule => $notice) {
            $noticesJson[] = [
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

        if ($warningsJson !== []) {
            $json['warnings'] = $warningsJson;
        }

        if ($noticesJson !== []) {
            $json['notices'] = $noticesJson;
        }

        $output->writeln(
            (string) json_encode($json, JSON_UNESCAPED_UNICODE),
            OutputInterface::OUTPUT_RAW,
        );

        return $violations === []
            ? self::SUCCESS
            : self::ERROR;
    }
}

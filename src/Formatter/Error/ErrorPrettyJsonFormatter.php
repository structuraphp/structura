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
final class ErrorPrettyJsonFormatter implements ErrorFormatterInterface
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
                    'rule' => $rule,
                    'message' => $violation->messageViolation,
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
                    'rule' => $rule,
                    'message' => $warning,
                ];
            }
        }

        $noticesJson = [];

        foreach ($notices as $rule => $notice) {
            $noticesJson[] = [
                'rule' => $rule,
                'message' => $notice,
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
            (string) json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            OutputInterface::OUTPUT_RAW,
        );

        return $violations === []
            ? self::SUCCESS
            : self::ERROR;
    }
}

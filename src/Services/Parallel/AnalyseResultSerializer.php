<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services\Parallel;

use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use StructuraPhp\Structura\ValueObjects\RuleDescriptionValueObject;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

/**
 * Converts an AnalyseValueObject to and from a plain array so it can travel between a worker
 * process and the parent as NDJSON.
 *
 * Only the flat, closure-free subset of the result crosses the boundary: rules are already
 * projected onto RuleDescriptionValueObject by the analyse service, and timeStart is deliberately
 * not transported since the parent owns the wall clock.
 */
final class AnalyseResultSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(AnalyseValueObject $analyseValueObject): array
    {
        return [
            'countPass' => $analyseValueObject->countPass,
            'countViolation' => $analyseValueObject->countViolation,
            'countWarning' => $analyseValueObject->countWarning,
            'countNotice' => $analyseValueObject->countNotice,
            'tests' => array_map(
                static fn (AnalyseTestValueObject $test): array => [
                    'source' => [
                        'testClassname' => $test->source->testClassname,
                        'textDox' => $test->source->textDox,
                        'methodName' => $test->source->methodName,
                        'line' => $test->source->line,
                        'pathname' => $test->source->pathname,
                    ],
                    'ruleDescriptions' => array_map(
                        static fn (RuleDescriptionValueObject $rule): array => [
                            'sourceCount' => $rule->sourceCount,
                            'fromFinder' => $rule->fromFinder,
                            'thatExpressions' => $rule->thatExpressions,
                        ],
                        $test->ruleDescriptions,
                    ),
                    'assert' => [
                        'pass' => $test->assertValueObject->pass,
                        'violations' => self::violationsToArray($test->assertValueObject->violations),
                        'exceptions' => $test->assertValueObject->exceptions,
                        'warnings' => $test->assertValueObject->warnings,
                        'notices' => $test->assertValueObject->notices,
                    ],
                ],
                $analyseValueObject->analyseTestValueObjects,
            ),
        ];
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @throws WorkerProtocolException when the payload does not match the expected shape
     */
    public function fromArray(array $data, float $timeStart): AnalyseValueObject
    {
        $tests = [];
        foreach ($this->arrayValue($data, 'tests') as $test) {
            if (!\is_array($test)) {
                throw new WorkerProtocolException('Malformed test entry in worker payload');
            }

            $tests[] = new AnalyseTestValueObject(
                source: $this->sourceFromArray($this->arrayValue($test, 'source')),
                ruleDescriptions: $this->ruleDescriptionsFromArray($this->arrayValue($test, 'ruleDescriptions')),
                assertValueObject: $this->assertFromArray($this->arrayValue($test, 'assert')),
            );
        }

        return new AnalyseValueObject(
            timeStart: $timeStart,
            countPass: $this->intValue($data, 'countPass'),
            countViolation: $this->intValue($data, 'countViolation'),
            countWarning: $this->intValue($data, 'countWarning'),
            countNotice: $this->intValue($data, 'countNotice'),
            analyseTestValueObjects: $tests,
        );
    }

    /**
     * @param array<string, array<int, ViolationValueObject>> $violations
     *
     * @return array<string, array<int, array<string, null|int|string>>>
     */
    private static function violationsToArray(array $violations): array
    {
        $result = [];
        foreach ($violations as $key => $violationList) {
            $result[$key] = array_map(
                static fn (ViolationValueObject $violation): array => [
                    'messageViolation' => $violation->messageViolation,
                    'assertClassname' => $violation->assertClassname,
                    'line' => $violation->line,
                    'pathname' => $violation->pathname,
                    'messageCustom' => $violation->messageCustom,
                ],
                $violationList,
            );
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $source
     */
    private function sourceFromArray(array $source): SourceTestValueObject
    {
        return new SourceTestValueObject(
            testClassname: $this->stringValue($source, 'testClassname'),
            textDox: $this->stringValue($source, 'textDox'),
            methodName: $this->stringValue($source, 'methodName'),
            line: $this->intValue($source, 'line'),
            pathname: $this->stringValue($source, 'pathname'),
        );
    }

    /**
     * @param array<array-key, mixed> $ruleDescriptions
     *
     * @return array<int, RuleDescriptionValueObject>
     */
    private function ruleDescriptionsFromArray(array $ruleDescriptions): array
    {
        $result = [];
        foreach ($ruleDescriptions as $rule) {
            if (!\is_array($rule)) {
                throw new WorkerProtocolException('Malformed rule description in worker payload');
            }

            /** @var null|array<int, string> $thatExpressions */
            $thatExpressions = $rule['thatExpressions'] ?? null;

            $result[] = new RuleDescriptionValueObject(
                sourceCount: $this->intValue($rule, 'sourceCount'),
                fromFinder: (bool) ($rule['fromFinder'] ?? false),
                thatExpressions: \is_array($thatExpressions) ? array_map(strval(...), $thatExpressions) : null,
            );
        }

        return $result;
    }

    /**
     * @param array<array-key, mixed> $assert
     */
    private function assertFromArray(array $assert): AssertValueObject
    {
        $violations = [];
        foreach ($this->arrayValue($assert, 'violations') as $key => $violationList) {
            if (!\is_array($violationList)) {
                throw new WorkerProtocolException('Malformed violation list in worker payload');
            }

            foreach ($violationList as $violation) {
                if (!\is_array($violation)) {
                    throw new WorkerProtocolException('Malformed violation in worker payload');
                }

                $pathname = $violation['pathname'] ?? null;

                $violations[(string) $key][] = new ViolationValueObject(
                    messageViolation: $this->stringValue($violation, 'messageViolation'),
                    assertClassname: $this->stringValue($violation, 'assertClassname'),
                    line: $this->intValue($violation, 'line'),
                    pathname: \is_string($pathname) ? $pathname : null,
                    messageCustom: $this->stringValue($violation, 'messageCustom'),
                );
            }
        }

        /** @var array<string, int> $pass */
        $pass = $this->arrayValue($assert, 'pass');

        /** @var array<string, array<int, string>> $exceptions */
        $exceptions = $this->arrayValue($assert, 'exceptions');

        /** @var array<string, array<int, string>> $warnings */
        $warnings = $this->arrayValue($assert, 'warnings');

        /** @var array<string, string> $notices */
        $notices = $this->arrayValue($assert, 'notices');

        return new AssertValueObject(
            pass: $pass,
            violations: $violations,
            exceptions: $exceptions,
            warnings: $warnings,
            notices: $notices,
        );
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return array<array-key, mixed>
     */
    private function arrayValue(array $data, string $key): array
    {
        $value = $data[$key] ?? [];

        return \is_array($value)
            ? $value
            : throw new WorkerProtocolException(
                \sprintf('Expected "%s" to be an array in worker payload', $key),
            );
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function stringValue(array $data, string $key): string
    {
        $value = $data[$key] ?? '';

        return \is_string($value)
            ? $value
            : throw new WorkerProtocolException(
                \sprintf('Expected "%s" to be a string in worker payload', $key),
            );
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function intValue(array $data, string $key): int
    {
        $value = $data[$key] ?? 0;

        return \is_int($value)
            ? $value
            : throw new WorkerProtocolException(
                \sprintf('Expected "%s" to be an integer in worker payload', $key),
            );
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Helper;

use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;

/**
 * Records the results an orchestrator hands back, in the order it hands them back.
 *
 * Used to assert emission order, which is the whole point of running the analysis in parallel
 * without changing the output.
 */
final class AnalyseResultRecorder
{
    /** @var array<int, AnalyseValueObject> */
    private array $results = [];

    public function record(AnalyseValueObject $result): void
    {
        $this->results[] = $result;
    }

    /**
     * @return array<int, int>
     */
    public function passCounts(): array
    {
        return array_map(
            static fn (AnalyseValueObject $result): int => $result->countPass,
            $this->results,
        );
    }

    /**
     * Test class names in the order their results were released.
     *
     * @return array<int, string>
     */
    public function classnames(): array
    {
        $classnames = [];
        foreach ($this->results as $result) {
            foreach ($result->analyseTestValueObjects as $test) {
                $classnames[] = $test->source->testClassname;
            }
        }

        return $classnames;
    }
}

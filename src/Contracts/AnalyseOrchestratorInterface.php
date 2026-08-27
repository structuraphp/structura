<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use Closure;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\Services\FinderService;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;

interface AnalyseOrchestratorInterface
{
    /**
     * Analyses every test class of the suite and merges the per-class results.
     *
     * @param null|(Closure(AnalyseValueObject): void) $onClassAnalysed called once per test class,
     *                                                                  in test suite declaration order
     *
     * @throws StopOnException when a stop-on-* threshold is reached
     */
    public function run(FinderService $finder, ?Closure $onClassAnalysed = null): AnalyseValueObject;
}

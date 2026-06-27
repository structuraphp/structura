<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use Closure;
use StructuraPhp\Structura\Exception\Console\StopOnException;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;

final readonly class AnalyseOrchestrator
{
    /**
     * @param array<string, string> $pathResolvers
     */
    public function __construct(
        private bool $stopOnError = false,
        private bool $stopOnWarning = false,
        private bool $stopOnNotice = false,
        private ?string $filter = null,
        private array $pathResolvers = [],
    ) {}

    /**
     * @param null|(Closure(AnalyseValueObject): void) $onClassAnalysed
     */
    public function run(FinderService $finder, ?Closure $onClassAnalysed = null): AnalyseValueObject
    {
        $timeStart = microtime(true);

        /** @var array<int, AnalyseValueObject> $results */
        $results = [];

        foreach ($finder->getClassTests() as $classname) {
            $service = new AnalyseService(
                stopOnError: $this->stopOnError,
                stopOnWarning: $this->stopOnWarning,
                stopOnNotice: $this->stopOnNotice,
                filter: $this->filter,
                pathResolvers: $this->pathResolvers,
            );

            try {
                $result = $service->analyse($timeStart, $classname);
            } catch (StopOnException $stopOnException) {
                $results[] = $stopOnException->analyseValueObject;
                if ($onClassAnalysed instanceof Closure) {
                    $onClassAnalysed($stopOnException->analyseValueObject);
                }

                throw new StopOnException(AnalyseValueObject::merge($timeStart, ...$results));
            }

            if ($onClassAnalysed instanceof Closure) {
                $onClassAnalysed($result);
            }

            $results[] = $result;
        }

        return AnalyseValueObject::merge($timeStart, ...$results);
    }
}

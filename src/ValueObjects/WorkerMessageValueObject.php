<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

/**
 * One validated protocol line coming back from a worker on its STDOUT.
 *
 * A worker answers a job with a single NDJSON line. Once that line has been decoded and its
 * shape checked, everything the parent needs from it is these three values: which class was
 * analysed, the serialized result to hand to AnalyseResultSerializer, and whether the worker
 * tripped a stop-on threshold. Error lines never reach this object -- they are turned into a
 * WorkerProtocolException while decoding.
 */
final readonly class WorkerMessageValueObject
{
    /**
     * @param array<array-key, mixed> $data result produced by AnalyseResultSerializer::toArray()
     */
    public function __construct(
        public string $classname,
        public array $data,
        public bool $stopOn,
    ) {}
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks;

use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use StructuraPhp\Structura\Enums\DescriptorType;
use StructuraPhp\Structura\Services\ParseService;

/**
 * Measures the cost of reading and parsing the corpus into descriptions.
 *
 * Serves as a reference point: a regression visible here but not in
 * AssertBench comes from the parser or the visitors, not from an assertion.
 */
#[Warmup(1)]
#[Revs(10)]
#[Iterations(5)]
final class ParseServiceBench
{
    public function benchParseClassLike(): void
    {
        $parseService = new ParseService(DescriptorType::ClassLike);

        iterator_to_array($parseService->parse(Corpus::finder()), false);
    }

    public function benchParseScript(): void
    {
        $parseService = new ParseService(DescriptorType::Script);

        iterator_to_array($parseService->parse(Corpus::finder()), false);
    }
}

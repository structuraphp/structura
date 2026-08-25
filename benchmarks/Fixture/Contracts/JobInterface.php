<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Contracts;

interface JobInterface
{
    public function handle(): void;
}

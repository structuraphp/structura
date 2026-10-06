<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Contracts;

interface ControllerInterface
{
    /**
     * @return array<string, string>
     */
    public function __invoke(): array;
}

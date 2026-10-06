<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Contracts;

use DateTimeImmutable;

interface EventInterface
{
    public function occurredAt(): DateTimeImmutable;
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Events;

use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final class ViolationEvent extends AbstractAnalysisEvent
{
    /**
     * @param array<int, ViolationValueObject> $violations
     */
    public function __construct(
        public readonly string $key,
        public readonly array $violations,
    ) {}
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Events;

final class PassEvent extends AbstractAnalysisEvent
{
    public function __construct(
        public readonly string $key,
    ) {}
}

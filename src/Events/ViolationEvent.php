<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Events;

use StructuraPhp\Structura\ValueObjects\ViolationValueObject;
use Symfony\Contracts\EventDispatcher\Event;

final class ViolationEvent extends Event
{
    /**
     * @param array<int, ViolationValueObject> $violations
     */
    public function __construct(
        public readonly string $key,
        public readonly array $violations,
    ) {}
}

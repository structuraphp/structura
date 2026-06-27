<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Events;

use Symfony\Contracts\EventDispatcher\Event;

final class WarningEvent extends Event
{
    public function __construct(
        public readonly string $key,
        public readonly string $message,
        public readonly bool $isAssertionWarning = false,
    ) {}
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Events;

use Symfony\Contracts\EventDispatcher\Event;

final class PassEvent extends Event
{
    public function __construct(
        public readonly string $key,
    ) {}
}

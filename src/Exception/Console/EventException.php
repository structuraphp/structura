<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Exception\Console;

use RuntimeException;
use Throwable;

class EventException extends RuntimeException
{
    public function __construct(
        public readonly object $event,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        $message = sprintf(
            'An error occurred while handling the event of type "%s".',
            get_class($event),
        );

        parent::__construct($message, $code, $previous);
    }
}

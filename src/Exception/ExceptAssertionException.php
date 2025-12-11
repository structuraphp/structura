<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Exception;

use InvalidArgumentException;
use Throwable;

class ExceptAssertionException extends InvalidArgumentException
{
    public function __construct(
        object $given,
        object $expected,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            sprintf(
                'assertion of type %s given, %s expected',
                $given::class,
                $expected::class,
            ),
            $code,
            $previous,
        );
    }
}

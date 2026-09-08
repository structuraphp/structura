<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Exception\Console;

use RuntimeException;

/**
 * Raised when a worker process emits something the parent cannot interpret as a valid
 * NDJSON message, or dies before returning the result of the class it was given.
 */
final class WorkerProtocolException extends RuntimeException {}

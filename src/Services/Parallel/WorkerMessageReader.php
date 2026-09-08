<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services\Parallel;

use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use StructuraPhp\Structura\ValueObjects\WorkerMessageValueObject;

/**
 * Reads the NDJSON protocol a single worker speaks on its STDOUT.
 *
 * A pipe carries bytes, not lines, so a read can stop in the middle of a message. push() holds
 * the incomplete tail until its newline arrives and only ever hands back whole lines; decode()
 * then turns one of those lines into a value object, or refuses it.
 *
 * One instance per worker: the buffer is per worker state, and mixing two workers' bytes in the
 * same buffer would produce lines that never existed.
 */
final class WorkerMessageReader
{
    /** @var string partial line still waiting for its newline */
    private string $buffer = '';

    /**
     * @param string $chunk bytes read from the worker since the last call
     *
     * @return array<int, string> complete, non-blank lines assembled so far
     */
    public function push(string $chunk): array
    {
        $this->buffer .= $chunk;

        $lines = explode("\n", $this->buffer);
        // The trailing element is either an empty string or a partial line: keep it buffered.
        $this->buffer = array_pop($lines);

        return array_values(array_filter($lines, static fn (string $line): bool => trim($line) !== ''));
    }

    /**
     * @throws WorkerProtocolException when the line is not JSON, carries a worker side error, or
     *                                 lacks the fields a result must have
     */
    public function decode(string $line): WorkerMessageValueObject
    {
        /** @var mixed $message */
        $message = json_decode($line, true);
        if (!\is_array($message)) {
            throw new WorkerProtocolException('Unreadable worker output: ' . $line);
        }

        if (($message['type'] ?? null) === 'error') {
            throw new WorkerProtocolException(
                \sprintf(
                    'Worker failed on "%s": %s',
                    \is_string($message['class'] ?? null) ? $message['class'] : 'unknown',
                    \is_string($message['message'] ?? null) ? $message['message'] : 'unknown error',
                ),
            );
        }

        $classname = $message['class'] ?? null;
        $data = $message['data'] ?? null;
        if (!\is_string($classname) || !\is_array($data)) {
            throw new WorkerProtocolException('Incomplete worker result: ' . $line);
        }

        return new WorkerMessageValueObject(
            classname: $classname,
            data: $data,
            stopOn: ($message['stopOn'] ?? false) === true,
        );
    }
}

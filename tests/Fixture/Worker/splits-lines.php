<?php

declare(strict_types=1);

/**
 * Stub worker answering in two writes, with no newline in the first one.
 *
 * Driven by WorkerPoolTest. A pipe carries bytes, not lines, so the parent has to buffer the
 * incomplete tail until its newline arrives; this stub makes that happen on purpose rather than
 * leaving it to chance.
 */
$stdin = fopen('php://stdin', 'rb');
if ($stdin === false) {
    exit(1);
}

while (($line = fgets($stdin)) !== false) {
    $job = json_decode(trim($line), true);
    if (!is_array($job) || !is_string($job['class'] ?? null)) {
        continue;
    }

    $message = json_encode([
        'type' => 'result',
        'class' => $job['class'],
        'stopOn' => false,
        'data' => ['countPass' => 1],
    ], JSON_THROW_ON_ERROR);

    $cut = (int) floor(strlen($message) / 2);

    fwrite(STDOUT, substr($message, 0, $cut));
    fflush(STDOUT);

    usleep(30000);

    fwrite(STDOUT, substr($message, $cut) . "\n");
    fflush(STDOUT);
}

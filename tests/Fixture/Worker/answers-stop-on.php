<?php

declare(strict_types=1);

/**
 * Stub worker answering every job with a result that tripped a stop-on threshold.
 *
 * Driven by WorkerPoolTest. A stop-on is not an error: the worker still returns a valid result,
 * only flagged, and the pool has to carry that flag through to its caller untouched.
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

    fwrite(STDOUT, json_encode([
        'type' => 'result',
        'class' => $job['class'],
        'stopOn' => true,
        'data' => ['countPass' => 1],
    ], JSON_THROW_ON_ERROR) . "\n");
    fflush(STDOUT);
}

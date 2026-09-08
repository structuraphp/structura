<?php

declare(strict_types=1);

/**
 * Stub worker answering every job with a valid protocol line.
 *
 * Driven by WorkerPoolTest, which points the pool's entry point here instead of bin/structura.
 * Nothing is analysed: the pool only has to be fed answers.
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
        'stopOn' => false,
        'data' => ['countPass' => 1],
    ], JSON_THROW_ON_ERROR) . "\n");
    fflush(STDOUT);
}

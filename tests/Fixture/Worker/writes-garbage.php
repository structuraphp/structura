<?php

declare(strict_types=1);

/**
 * Stub worker writing a line that is not the protocol.
 *
 * Driven by WorkerPoolTest. This is what a stray echo, var_dump or PHP warning in the analysis
 * path does to the pool: the line parser trips on something that is not JSON. The worker stays
 * alive afterwards, so the parent reports the bad line rather than a dead process.
 */
$stdin = fopen('php://stdin', 'rb');
if ($stdin === false) {
    exit(1);
}

while (fgets($stdin) !== false) {
    fwrite(STDOUT, "not json\n");
    fflush(STDOUT);
}

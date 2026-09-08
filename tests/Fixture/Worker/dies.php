<?php

declare(strict_types=1);

/**
 * Stub worker dying on its first job without answering it.
 *
 * Driven by WorkerPoolTest. Reproduces what a worker failing to boot looks like from the pool: a
 * job handed out, nothing ever returned, and an exit code plus a STDERR message to report -- the
 * shape the worker autoload bug surfaced as.
 */
$stdin = fopen('php://stdin', 'rb');
if ($stdin === false) {
    exit(1);
}

if (fgets($stdin) !== false) {
    fwrite(STDERR, 'stub could not boot');

    exit(3);
}

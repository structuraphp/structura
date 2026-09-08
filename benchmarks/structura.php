<?php

declare(strict_types=1);

use StructuraPhp\Structura\Contracts\StructuraConfigInterface;

/*
 * Configuration used by the parallel benchmark only: worker processes reload it to rebuild the
 * exact same test suite the parent measured. Kept in sync with AnalyseOrchestratorBench::setUp().
 */
return static function (StructuraConfigInterface $config): void {
    $config->addTestSuite(__DIR__ . '/Suite', 'bench');
};

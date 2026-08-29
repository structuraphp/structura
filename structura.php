<?php

declare(strict_types=1);

use StructuraPhp\Structura\Contracts\StructuraConfigInterface;

return static function (StructuraConfigInterface $config): void {
    $config->addTestSuite('tests/Feature', 'main');
    $config->archiRootNamespace(
        'StructuraPhp\Structura\Tests\Feature',
        'tests/Feature',
    );

    $config->setProcesses(4);
    // $config->setAutoload(__DIR__ . '/vendor/autoload.php');
};

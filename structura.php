<?php

declare(strict_types=1);

use StructuraPhp\Structura\Configs\StructuraConfig;

return static function (StructuraConfig $config): void {
    $config->archiRootNamespace(
        'StructuraPhp\Structura\Tests\Feature',
        'tests/Feature',
    );
};

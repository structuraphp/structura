<?php

declare(strict_types=1);

use Structura\Configs\StructuraConfig;

return static function (StructuraConfig $archiConfig): void {
    $archiConfig
        ->archiRootNamespace(
            'Structura\Tests\Architecture',
            'tests/Architecture',
        );
};

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks\Fixture\Support;

final class Bootstrap
{
    private const CONFIG_PATH = '/config/app.php';

    public function load(): void
    {
        require_once __DIR__ . self::CONFIG_PATH;

        include __DIR__ . '/../routes/web.php';
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use Symfony\Component\Console\Style\SymfonyStyle;

final class AutoloadService
{
    public function load(?string $autoload, SymfonyStyle $output): void
    {
        if ($autoload === null) {
            $output->warning(
                'Running inside a PHAR archive without autoload configuration: '
                . 'custom rules and formatters cannot be loaded. '
                . 'Set it in structura.php, for example: $config->setAutoload(__DIR__ . "/vendor/autoload.php").',
            );

            return;
        }

        if (!is_file($autoload)) {
            $output->error(
                \sprintf(
                    'The autoload file "%s" could not be found. For example: __DIR__ . "/vendor/autoload.php".',
                    $autoload,
                ),
            );

            return;
        }

        require $autoload;
    }
}

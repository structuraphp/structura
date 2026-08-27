<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Console;

use Closure;
use InvalidArgumentException;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Loads the structura.php configuration file and, inside a PHAR, the analysed project autoload.
 *
 * Shared by every command that needs the user configuration, including the worker processes
 * spawned in parallel mode: a worker re-requires the very same file rather than receiving a
 * serialized configuration, which would be impossible since the config carries user formatter
 * instances.
 */
trait LoadsConfig
{
    private function loadConfigValueObject(string $configPath): ConfigValueObject
    {
        /** @var Closure(StructuraConfig): void|StructuraConfig $closure */
        $closure = require $configPath;
        if (!$closure instanceof Closure) {
            throw new InvalidArgumentException(
                \sprintf('The configuration file "%s" must return a closure.', $configPath),
            );
        }

        $config = new StructuraConfig();
        $closure($config);

        return $config->getConfig();
    }

    private function autoloadProject(ConfigValueObject $config, SymfonyStyle $output): void
    {
        if (!str_starts_with(__FILE__, 'phar://')) {
            return;
        }

        if (!is_string($config->autoload)) {
            $output->warning(
                'This command is running inside a PHAR archive but no autoload file is configured. '
                . 'Declare one with $config->setAutoload(__DIR__ . "/vendor/autoload.php") '
                . 'if your assertions rely on your project classes.',
            );

            return;
        }

        if (is_file($config->autoload)) {
            require $config->autoload;

            return;
        }

        $output->error(
            sprintf(
                'The autoload file "%s" could not be found. For example: __DIR__ . "/vendor/autoload.php".',
                $config->autoload,
            ),
        );
    }
}

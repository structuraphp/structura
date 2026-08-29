<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services\Parallel;

use Composer\Autoload\ClassLoader;
use Phar;
use ReflectionClass;

/**
 * Locates the Composer autoload file the current process booted on.
 *
 * Worker processes are fresh PHP processes: they have to require an autoload file before any
 * Structura class exists, so they cannot be told which one through the configuration. The parent
 * therefore resolves it here and forwards it in the worker environment. Resolution starts from
 * the ClassLoader actually in memory rather than from a path relative to this package, which
 * would point at the wrong vendor directory whenever the package is installed as a dependency or
 * symlinked by a path repository.
 */
final readonly class ComposerAutoloadLocator
{
    /** @var string environment variable read back by bin/structura */
    public const ENV_VARIABLE = 'STRUCTURA_COMPOSER_AUTOLOAD';

    /**
     * @return null|string absolute path to vendor/autoload.php, null when it cannot be resolved
     *                     or when the current process runs from a PHAR, which carries its own
     */
    public function locate(): ?string
    {
        if (Phar::running(false) !== '') {
            return null;
        }

        $classLoader = (new ReflectionClass(ClassLoader::class))->getFileName();
        if (!\is_string($classLoader)) {
            return null;
        }

        // vendor/composer/ClassLoader.php -> vendor/autoload.php
        $autoload = \dirname($classLoader, 2) . '/autoload.php';

        return is_file($autoload) ? $autoload : null;
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use InvalidArgumentException;

interface StructuraConfigInterface
{
    public function setErrorFormatter(
        string $name,
        ErrorFormatterInterface $errorFormatter,
    ): self;

    public function setProgressFormatter(
        string $name,
        ProgressFormatterInterface $progressFormatter,
    ): self;

    /**
     * @param array<int,string> $extensions
     */
    public function fileExtensions(array $extensions): self;

    /**
     * A test suite must be defined in order to run the architecture analysis command.
     *
     * @param string $path relative path to the architecture tests directory (e.g. “tests/Architecture”)
     * @param string $name unique name of the test suite (e.g. “main,” “default,” etc.)
     */
    public function addTestSuite(string $path, string $name): self;

    /**
     * The root namespace is required to use the test creation command.
     *
     * @param string $namespace root namespace (e.g ”Acme\Tests\Architecture”)
     * @param string $directory relative root path to the architecture tests directory (e.g. “tests/Architecture”)
     */
    public function archiRootNamespace(string $namespace, string $directory): self;

    /**
     * @param string $path absolute path to your project's autoload file if you are using PHAR (e.g "__DIR__ . '/vendor/autoload.php")
     */
    public function setAutoload(string $path): self;

    /**
     * Number of processes used to analyse the test suite.
     *
     * Test classes are distributed across that many worker processes. The default of 1 keeps the
     * analysis sequential. Parallel runs produce the exact same output as sequential ones, but a
     * listener registered on the orchestration event bus never sees the events dispatched inside
     * the workers.
     *
     * @param int $processes number of processes, must be greater than or equal to 1
     *
     * @throws InvalidArgumentException if $processes is lower than 1
     */
    public function setProcesses(int $processes): self;

    /**
     * Uses as many processes as there are usable CPU cores, falling back to 1 when the core count
     * cannot be detected. Resolved immediately, at configuration time.
     */
    public function setProcessesAuto(): self;

    /**
     * Registers a custom function name as a path resolver for the `toUseInclude` assertion.
     * When the function name is found in an include/require expression in the AST,
     * its arguments are ignored and the registered path is returned directly.
     *
     * The function name `dirname` is reserved and cannot be registered.
     *
     * @param string $functionName the function name to resolve (e.g. "base_path", "app_path")
     * @param string $path the absolute or relative path the function represents (e.g. "/var/www")
     *
     * @throws InvalidArgumentException if $functionName is "dirname"
     */
    public function addPathResolver(string $functionName, string $path): self;
}

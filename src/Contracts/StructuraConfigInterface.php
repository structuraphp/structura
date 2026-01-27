<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

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
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Configs;

use InvalidArgumentException;
use StructuraPhp\Structura\Contracts\ErrorFormatterInterface;
use StructuraPhp\Structura\Contracts\ProgressFormatterInterface;
use StructuraPhp\Structura\Contracts\StructuraConfigInterface;
use StructuraPhp\Structura\ValueObjects\ConfigValueObject;
use StructuraPhp\Structura\ValueObjects\RootNamespaceValueObject;

class StructuraConfig implements StructuraConfigInterface
{
    /** @var array<int,string> */
    private array $extensions = [];

    /** @var array<string,ErrorFormatterInterface> */
    private array $errorFormatter = [];

    /** @var array<string,ProgressFormatterInterface> */
    private array $progressFormatter = [];

    private ?RootNamespaceValueObject $archiRootNamespace = null;

    /** @var array<string, string> */
    private array $testSuites = [];

    private ?string $autoload = null;

    /** @var array<string, string> */
    private array $pathResolvers = [];

    public static function make(): self
    {
        return new self();
    }

    public function setErrorFormatter(
        string $name,
        ErrorFormatterInterface $errorFormatter,
    ): self {
        $this->errorFormatter[$name] = $errorFormatter;

        return $this;
    }

    public function setProgressFormatter(
        string $name,
        ProgressFormatterInterface $progressFormatter,
    ): self {
        $this->progressFormatter[$name] = $progressFormatter;

        return $this;
    }

    /**
     * @param array<int,string> $extensions
     */
    public function fileExtensions(array $extensions): self
    {
        $this->extensions = $extensions;

        return $this;
    }

    public function addTestSuite(string $path, string $name): self
    {
        $this->testSuites[$name] = $path;

        return $this;
    }

    public function archiRootNamespace(string $namespace, string $directory): self
    {
        $this->archiRootNamespace = new RootNamespaceValueObject(
            namespace: $namespace,
            directory: $directory,
        );

        return $this;
    }

    public function setAutoload(string $path): StructuraConfigInterface
    {
        $this->autoload = $path;

        return $this;
    }

    public function addPathResolver(string $functionName, string $path): self
    {
        if ($functionName === 'dirname') {
            throw new InvalidArgumentException(
                'The function name "dirname" is reserved and cannot be registered as a path resolver.',
            );
        }

        $this->pathResolvers[$functionName] = $path;

        return $this;
    }

    public function getConfig(): ConfigValueObject
    {
        return new ConfigValueObject(
            testSuites: $this->testSuites,
            rootNamespace: $this->archiRootNamespace,
            errorFormatter: $this->errorFormatter,
            progressFormatter: $this->progressFormatter,
            extensions: $this->extensions,
            autoload: $this->autoload,
            pathResolvers: $this->pathResolvers,
        );
    }
}

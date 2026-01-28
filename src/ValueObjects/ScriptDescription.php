<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use PhpParser\Node\Stmt\Declare_;
use StructuraPhp\Structura\Enums\DependenciesType;

class ScriptDescription
{
    /** @var array<int,string> */
    protected array $classDependencies = [];

    /** @var array<int,string> */
    protected array $functionDependencies = [];

    protected ?string $fileBasename = null;

    public function __construct(
        public readonly ?string $namespace,
        public readonly ?Declare_ $declare,
    ) {}

    /**
     * @return array<int,string>
     */
    public function getClassDependencies(): array
    {
        return $this->classDependencies;
    }

    /**
     * @return array<int,string>
     */
    public function getFunctionDependencies(): array
    {
        return $this->functionDependencies;
    }

    /**
     * @param array<int,string> $classDependencies
     */
    public function setClassDependencies(array $classDependencies): self
    {
        $this->classDependencies = $classDependencies;

        return $this;
    }

    /**
     * @param array<int,string> $dependencies
     */
    public function setFunctionDependencies(array $dependencies): self
    {
        $this->functionDependencies = $dependencies;

        return $this;
    }

    public function getFileBasename(): ?string
    {
        return $this->fileBasename;
    }

    public function setFilePathname(?string $fileBasename): self
    {
        $this->fileBasename = $fileBasename;

        return $this;
    }

    public function hasDeclare(
        string $key,
        string $value,
    ): bool {
        if (!$this->declare instanceof Declare_) {
            return false;
        }

        foreach ($this->declare->declares as $declare) {
            if (
                $declare->key->name === $key
                && $declare->value->getAttribute('rawValue') === $value
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int,string> $patterns
     *
     * @return array<int,string>
     */
    public function getDependenciesByPatterns(
        array $patterns,
        DependenciesType $type = DependenciesType::All,
    ): array {
        $matches = [];
        if ($patterns === []) {
            return [];
        }

        $pattern = implode('|', $patterns);

        /** @var array<int,string>|false $match */
        $match = preg_grep(
            '/^(?:' . $this->customPregQuote($pattern) . ')$/',
            $this->getClassDependencies(),
        );

        if ($match !== false) {
            return array_merge($matches, $match);
        }

        return $matches;
    }

    /**
     * @param array<int,string> $patterns
     *
     * @return array<int,string>
     */
    public function getDependenciesFunctionByPatterns(
        array $patterns,
    ): array {
        if ($patterns === []) {
            return [];
        }

        $pattern = implode('|', $patterns);

        /** @var array<int,string>|false $match */
        $match = preg_grep(
            '/^' . $this->customPregQuote($pattern) . '$/',
            $this->getFunctionDependencies(),
        );

        if ($match !== false) {
            return $match;
        }

        return [];
    }

    /**
     * @param array<int,string> $patterns
     */
    public function hasNamespaceByPatterns(array $patterns): bool
    {
        if ($patterns === []) {
            return false;
        }

        $pattern = implode('|', $patterns);

        return (bool) preg_match(
            '/^' . $this->customPregQuote($pattern) . '$/',
            $this->namespace ?? '',
        );
    }

    /**
     * @param array<int,string> $allowedCharacters
     */
    protected function customPregQuote(
        string $subject,
        array $allowedCharacters = ['^', '$', '\\'],
    ): string {
        $mapping = [];
        foreach ($allowedCharacters as $char) {
            $mapping[$char] = '\\' . $char;
        }

        return strtr($subject, $mapping);
    }
}

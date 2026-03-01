<?php

declare(strict_types=1);

namespace StructuraPhp\Structura;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;

class Except
{
    /** @var array<class-string, array<class-string<ExprInterface>, true>> */
    private array $classNameExpects = [];

    /** @var array<class-string<ExprInterface>, array<string, true>> */
    private array $fileNameExpects = [];

    /** @var array<class-string<ExprInterface>, array<string, true>> */
    private array $namespaceExpects = [];

    /**
     * @param array<int, class-string>|class-string $className
     * @param class-string<ExprInterface> $expression
     */
    public function byClassname(
        array|string $className,
        string $expression,
    ): self {
        $classNames = (array) $className;

        foreach ($classNames as $class) {
            $this->classNameExpects[$class][$expression] = true;
        }

        return $this;
    }

    /**
     * Ignore errors for scripts matching a filename pattern (regex).
     *
     * @param array<string>|string $filePattern Regex pattern (e.g., '/path\/to\/.+\.php$/')
     * @param class-string<ExprInterface> $expression Rule(s) to exclude
     */
    public function byFileName(
        array|string $filePattern,
        string $expression,
    ): self {
        $patterns = (array) $filePattern;

        foreach ($patterns as $pattern) {
            $this->fileNameExpects[$expression][$pattern] = true;
        }

        return $this;
    }

    /**
     * Ignore errors for scripts in specific namespaces (regex pattern).
     *
     * @param array<string>|string $namespace Namespace pattern(s) (e.g., 'App\Tests' or 'App\Tests\.*')
     * @param class-string<ExprInterface> $expression Rule(s) to exclude
     */
    public function byNamespace(
        array|string $namespace,
        string $expression,
    ): self {
        $namespaces = (array) $namespace;

        foreach ($namespaces as $ns) {
            $this->namespaceExpects[$expression][$ns] = true;
        }

        return $this;
    }

    /**
     * @param class-string<AbstractExpr|ExprInterface> $expression
     */
    public function isExcept(ScriptDescription $description, string $expression): bool
    {
        // Check by class name
        $className = $description->namespace;
        if (
            is_string($className)
            && $className !== ''
            && isset($this->classNameExpects[$className][$expression])
        ) {
            return true;
        }

        $filename = $description->getFileBasename();
        if (
            $filename !== ''
            && $this->checkFilePatterns($filename, $expression)
        ) {
            return true;
        }

        return is_string($className)
            && $className !== ''
            && $this->checkNamespacePatterns($className, $expression);
    }

    private function checkFilePatterns(string $filename, string $expression): bool
    {
        if (!isset($this->fileNameExpects[$expression])) {
            return false;
        }

        foreach (array_keys($this->fileNameExpects[$expression]) as $pattern) {
            if ($this->patternMatches($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }

    private function checkNamespacePatterns(string $namespace, string $expression): bool
    {
        if (!isset($this->namespaceExpects[$expression])) {
            return false;
        }

        foreach (array_keys($this->namespaceExpects[$expression]) as $pattern) {
            if ($this->patternMatches($pattern, $namespace)) {
                return true;
            }
        }

        return false;
    }

    private function patternMatches(string $pattern, string $subject): bool
    {
        if ($pattern === $subject) {
            return true;
        }

        if (str_starts_with($pattern, '/') && str_ends_with($pattern, '/')) {
            return (bool) preg_match($pattern, $subject);
        }

        if (str_contains($pattern, '\\')) {
            return (bool) preg_match(
                '/^' . $this->customPregQuote($pattern) . '$/',
                $subject,
            );
        }

        return false;
    }

    /**
     * @param array<int,string> $allowedCharacters
     */
    private function customPregQuote(
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

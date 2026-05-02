<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Concerns\Arr;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Enums\DependenciesType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotDependsOnPhpDoc implements ExprScriptInterface
{
    use Arr;

    /**
     * @param array<int,class-string> $names
     * @param array<int,string> $patterns
     */
    public function __construct(
        private array $names,
        private array $patterns,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf(
            'to not depends on phpDoc <promote>%s</promote>',
            $this->implodeMore(array_merge($this->names, $this->patterns)),
        );
    }

    public function assert(ScriptDescription $description): bool
    {
        $dependencies = array_merge(
            $this->names,
            $description->getDependenciesByPatterns($this->patterns, DependenciesType::PhpDoc),
        );

        return array_intersect($description->getDocBlockDependencies(), $dependencies) === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ScriptDescription $description): array
    {
        $unauthorizedDependence = implode(', ', array_merge($this->names, $this->patterns));
        $dependencies = array_merge(
            $this->names,
            $description->getDependenciesByPatterns($this->patterns, DependenciesType::PhpDoc),
        );
        $violations = array_intersect($description->getDocBlockDependencies(), $dependencies);
        sort($violations);

        $results = [];
        foreach ($violations as $violation) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must not depends on these phpDoc namespaces %s but depends on <fire>%s</fire>',
                    $description->getResourceName(),
                    $unauthorizedDependence,
                    $violation,
                ),
                $this::class,
                $description instanceof ClassDescription
                    ? $description->lines
                    : 0,
                $description->getFileBasename(),
                $this->message,
            );
        }

        return $results;
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Concerns\Arr;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotDependsOn implements ExprScriptInterface
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
            'to not depends on these namespaces <promote>%s</promote>',
            $this->implodeMore(array_merge($this->names, $this->patterns)),
        );
    }

    public function assert(ScriptDescription $description): bool
    {
        $dependencies = array_merge(
            $this->names,
            $description->getDependenciesByPatterns($this->patterns),
        );

        return array_intersect($description->getClassDependencies(), $dependencies) === [];
    }

    public function getViolation(ScriptDescription $description): ViolationValueObject
    {
        return $description instanceof ClassDescription
            ? $this->getViolationClass($description)
            : $this->getViolationScript($description);
    }

    private function getViolationClass(ClassDescription $class): ViolationValueObject
    {
        $unauthorizedDependence = array_merge($this->names, $this->patterns);
        $dependencies = array_merge(
            $this->names,
            $class->getDependenciesByPatterns($this->patterns),
        );
        $violations = array_intersect($class->getClassDependencies(), $dependencies);
        sort($violations);

        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not depends on these namespaces %s but depends on <fire>%s</fire>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                implode(', ', $unauthorizedDependence),
                implode(', ', $violations),
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }

    private function getViolationScript(ScriptDescription $script): ViolationValueObject
    {
        $unauthorizedDependence = array_merge($this->names, $this->patterns);
        $dependencies = array_merge(
            $this->names,
            $script->getDependenciesByPatterns($this->patterns),
        );
        $violations = array_intersect($script->getClassDependencies(), $dependencies);
        sort($violations);

        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not depends on these namespaces %s but depends on <fire>%s</fire>',
                $script->namespace ?? $script->getFileBasename(),
                implode(', ', $unauthorizedDependence),
                implode(', ', $violations),
            ),
            $this::class,
            0,
            $script->getFileBasename(),
            $this->message,
        );
    }
}

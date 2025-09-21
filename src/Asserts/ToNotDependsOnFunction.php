<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Concerns\Arr;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotDependsOnFunction implements ExprScriptInterface
{
    use Arr;

    /**
     * @param array<int,string> $names
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
            'not depends on function <promote>%s</promote>',
            $this->implodeMore(array_merge($this->names, $this->patterns)),
        );
    }

    public function assert(ScriptDescription $description): bool
    {
        $dependencies = array_merge(
            $this->names,
            $description->getDependenciesFunctionByPatterns($this->patterns),
        );

        return array_intersect(
            $description->getFunctionDependencies(),
            array_unique($dependencies),
        ) === [];
    }

    public function getViolation(ScriptDescription $description): ViolationValueObject
    {
        return $description instanceof ClassDescription
            ? $this->getViolationClass($description)
            : $this->getViolationScript($description);
    }

    private function getViolationClass(ClassDescription $class): ViolationValueObject
    {
        $authorisedDependence = array_merge($this->names, $this->patterns);
        $dependencies = array_merge(
            $this->names,
            $class->getDependenciesFunctionByPatterns($this->patterns),
        );
        $violations = array_intersect(
            $class->getFunctionDependencies(),
            array_unique($dependencies),
        );
        sort($violations);

        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not depends on functions %s but depends on %s',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                implode(', ', $authorisedDependence),
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
        $authorisedDependence = array_merge($this->names, $this->patterns);
        $dependencies = array_merge(
            $this->names,
            $script->getDependenciesFunctionByPatterns($this->patterns),
        );
        $violations = array_intersect(
            $script->getFunctionDependencies(),
            array_unique($dependencies),
        );
        sort($violations);

        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not depends on functions %s but depends on %s',
                $script->namespace ?? $script->getFileBasename(),
                implode(', ', $authorisedDependence),
                implode(', ', $violations),
            ),
            $this::class,
            0,
            $script->getFileBasename(),
            $this->message,
        );
    }
}

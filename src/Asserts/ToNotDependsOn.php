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
        $dependencies = array_merge(
            $this->names,
            $class->getDependenciesByPatterns($this->patterns),
        );
        $dependencies = array_intersect($class->getClassDependencies(), $dependencies);
        sort($dependencies);

        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not depends on these namespaces %s',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $this->implodeMore($dependencies),
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }

    private function getViolationScript(ScriptDescription $script): ViolationValueObject
    {
        $dependencies = array_merge(
            $this->names,
            $script->getDependenciesByPatterns($this->patterns),
        );
        $dependencies = array_intersect($script->getClassDependencies(), $dependencies);
        sort($dependencies);

        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not depends on these namespaces %s',
                $script->namespace ?? '',
                $this->implodeMore($dependencies),
            ),
            $this::class,
            0,
            $script->getFileBasename(),
            $this->message,
        );
    }
}

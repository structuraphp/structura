<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Concerns\Arr;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotDependsOn implements ExprInterface
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

    public function assert(ClassDescription $class): bool
    {
        $dependencies = array_merge(
            $this->names,
            $class->getDependenciesByPatterns($this->patterns),
        );

        return array_intersect($class->getClassDependencies(), $dependencies) === [];
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
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
}

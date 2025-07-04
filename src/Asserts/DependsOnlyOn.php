<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Concerns\Arr;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class DependsOnlyOn implements ExprInterface
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
            'depends only on these namespaces <promote>%s</promote>',
            $this->implodeMore(array_merge($this->names, $this->patterns)),
        );
    }

    public function assert(ClassDescription $class): bool
    {
        $dependencies = array_merge(
            $this->names,
            $class->getDependenciesByPatterns($this->patterns),
        );

        return array_diff($class->getClassDependencies(), array_unique($dependencies)) === [];
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        $authorisedDependence = array_merge($this->names, $this->patterns);
        $dependencies = array_merge(
            $this->names,
            $class->getDependenciesByPatterns($this->patterns),
        );
        $violations = array_diff($class->getClassDependencies(), $dependencies);
        sort($violations);

        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must depends only on these namespaces %s but depends %s',
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
}

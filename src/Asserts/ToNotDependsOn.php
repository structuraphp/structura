<?php

declare(strict_types=1);

namespace Structura\Asserts;

use Structura\Concerns\Arr;
use Structura\Contracts\ExprInterface;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

class ToNotDependsOn implements ExprInterface
{
    use Arr;

    /**
     * @param array<int,class-string> $names
     * @param array<int,string> $patterns
     */
    public function __construct(
        private readonly array $names,
        private readonly array $patterns,
        private readonly string $message = '',
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
            $class->pregGrepAll($this->patterns),
        );

        return array_intersect($class->getDependencies(), $dependencies) === [];
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        $dependencies = array_merge(
            $this->names,
            $class->pregGrepAll($this->patterns),
        );
        $dependencies = array_intersect($class->getDependencies(), $dependencies);
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

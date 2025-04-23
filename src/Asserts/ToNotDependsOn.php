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
     */
    public function __construct(
        private readonly array $names,
    ) {}

    public function __toString(): string
    {
        return \sprintf(
            'to not depends on these namespaces <promote>%s</promote>',
            $this->implodeMore($this->names),
        );
    }

    public function assert(ClassDescription $class): bool
    {
        return array_intersect($class->getDependencies(), $this->names) === [];
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        $dependencies = array_intersect($class->getDependencies(), $this->names);
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
            '',
        );
    }
}

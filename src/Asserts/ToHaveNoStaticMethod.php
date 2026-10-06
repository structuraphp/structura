<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Stmt\ClassMethod;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToHaveNoStaticMethod implements ExprInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to have no static method';
    }

    public function assert(ClassDescription $class): bool
    {
        return $this->getStaticMethods($class) === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        $results = [];
        foreach ($this->getStaticMethods($class) as $method) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must have no static method but has <fire>%s</fire>',
                    $class->getResourceName(),
                    $method->name->toString(),
                ),
                $this::class,
                $method->getLine(),
                $class->getFileBasename(),
                $this->message,
            );
        }

        return $results;
    }

    /**
     * @return array<int, ClassMethod>
     */
    private function getStaticMethods(ClassDescription $class): array
    {
        return array_values(array_filter(
            $class->methods ?? [],
            static fn (ClassMethod $method): bool => $method->isStatic(),
        ));
    }
}

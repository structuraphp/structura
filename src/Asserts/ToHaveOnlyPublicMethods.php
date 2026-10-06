<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Stmt\ClassMethod;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToHaveOnlyPublicMethods implements ExprInterface
{
    /**
     * @param array<int, string> $names
     */
    public function __construct(
        private array $names,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to have only public methods <promote>%s</promote>', implode(', ', $this->names));
    }

    public function assert(ClassDescription $class): bool
    {
        return $this->getForbiddenMethods($class) === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        $results = [];
        foreach ($this->getForbiddenMethods($class) as $method) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must have only public methods <promote>%s</promote> but has public method <fire>%s</fire>',
                    $class->getResourceName(),
                    implode(', ', $this->names),
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
     * Public methods whose name is not allowed (PHP method names are case-insensitive).
     *
     * @return array<int, ClassMethod>
     */
    private function getForbiddenMethods(ClassDescription $class): array
    {
        $allowed = array_map(strtolower(...), $this->names);

        return array_values(array_filter(
            $class->methods ?? [],
            static fn (ClassMethod $method): bool => $method->isPublic()
                && !\in_array($method->name->toLowerString(), $allowed, true),
        ));
    }
}

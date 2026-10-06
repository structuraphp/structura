<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotImplement implements ExprInterface
{
    /** @var array<int,class-string> */
    private array $names;

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function __construct(
        array|string $names,
        private string $message = '',
    ) {
        $this->names = (array) $names;
    }

    public function __toString(): string
    {
        return \sprintf(
            'to not implement <promote>%s</promote>',
            implode(', ', $this->names),
        );
    }

    public function assert(ClassDescription $class): bool
    {
        return array_intersect($class->interfaces ?? [], $this->names) === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        $results = [];
        foreach (array_intersect($class->interfaces ?? [], $this->names) as $violation) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must not implement <fire>%s</fire>',
                    $class->getResourceName(),
                    $violation,
                ),
                $this::class,
                $violation->getLine(),
                $class->getFileBasename(),
                $this->message,
            );
        }

        return $results;
    }
}

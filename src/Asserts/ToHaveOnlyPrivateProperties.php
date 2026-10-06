<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\PropertyValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToHaveOnlyPrivateProperties implements ExprInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to have only private properties';
    }

    public function assert(ClassDescription $class): bool
    {
        return $this->getNonPrivateProperties($class) === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        $results = [];
        foreach ($this->getNonPrivateProperties($class) as $property) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must have only private properties but <fire>$%s</fire> is %s',
                    $class->getResourceName(),
                    $property->name,
                    $property->visibility->label(),
                ),
                $this::class,
                $property->line,
                $class->getFileBasename(),
                $this->message,
            );
        }

        return $results;
    }

    /**
     * @return array<int, PropertyValueObject>
     */
    private function getNonPrivateProperties(ClassDescription $class): array
    {
        return array_filter(
            $class->properties,
            static fn (PropertyValueObject $property): bool => !$property->isPrivate(),
        );
    }
}

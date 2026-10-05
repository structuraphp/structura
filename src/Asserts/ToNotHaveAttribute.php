<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotHaveAttribute implements ExprInterface
{
    /**
     * @param class-string $name
     */
    public function __construct(
        private string $name,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to not have attribute <promote>%s</promote>', $this->name);
    }

    public function assert(ClassDescription $class): bool
    {
        return !$class->hasAttribute($this->name);
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        $results = [];
        foreach ($class->getAttributeNames() as $attribute) {
            if ($attribute->toString() !== $this->name) {
                continue;
            }

            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must not have attribute <fire>%s</fire>',
                    $class->getResourceName(),
                    $this->name,
                ),
                $this::class,
                $attribute->getLine(),
                $class->getFileBasename(),
                $this->message,
            );
        }

        return $results;
    }
}

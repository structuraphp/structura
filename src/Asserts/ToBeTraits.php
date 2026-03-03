<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToBeTraits implements ExprInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to be traits';
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->classType === ClassType::Trait_;
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        return [
            new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must be a trait but is <fire>%s</fire>',
                    $class->getResourceName(),
                    $class->classType->label(),
                ),
                $this::class,
                $class->lines,
                $class->getFileBasename(),
                $this->message,
            ),
        ];
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToImplementNothing implements ExprInterface
{
    public function __construct(
        public string $message,
    ) {}

    public function __toString(): string
    {
        return 'to implement nothing';
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->interfaces === null || $class->interfaces === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        return [
            new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must not implement anything but implement <fire>%s</fire>',
                    $class->getResourceName(),
                    implode(', ', $class->interfaces ?? []),
                ),
                $this::class,
                $class->lines,
                $class->getFileBasename(),
                $this->message,
            ),
        ];
    }
}

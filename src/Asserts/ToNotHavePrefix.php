<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotHavePrefix implements ExprInterface
{
    public function __construct(
        private string $prefix,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to not have prefix <promote>%s</promote>', $this->prefix);
    }

    public function assert(ClassDescription $class): bool
    {
        return !str_starts_with($class->name ?? '', $this->prefix);
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        return [
            new ViolationValueObject(
                \sprintf(
                    'Resource name <promote>%s</promote> must not start with <fire>%s</fire>',
                    $class->getResourceName(),
                    $this->prefix,
                ),
                $this::class,
                $class->lines,
                $class->getFileBasename(),
                $this->message,
            ),
        ];
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotHaveMethod implements ExprInterface
{
    public function __construct(
        private string $name,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to not have method <promote>%s</promote>', $this->name);
    }

    public function assert(ClassDescription $class): bool
    {
        return !$class->hasMethods($this->name);
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        foreach ($class->methods ?? [] as $method) {
            if ($method->name->name !== $this->name) {
                continue;
            }

            return [
                new ViolationValueObject(
                    \sprintf(
                        'Resource <promote>%s</promote> must not have method <fire>%s</fire>',
                        $class->getResourceName(),
                        $this->name,
                    ),
                    $this::class,
                    $method->getLine(),
                    $class->getFileBasename(),
                    $this->message,
                ),
            ];
        }

        return [];
    }
}

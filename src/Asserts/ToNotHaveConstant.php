<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\VisibilityType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotHaveConstant implements ExprInterface
{
    public function __construct(
        private VisibilityType $visibility,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to not have <promote>%s</promote> constant', $this->visibility->value);
    }

    public function assert(ClassDescription $class): bool
    {
        return match ($this->visibility) {
            VisibilityType::Public => !$class->hasPublicConstant(),
            VisibilityType::Protected => !$class->hasProtectedConstant(),
            VisibilityType::Private => !$class->hasPrivateConstant(),
        };
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must not have <promote>%s</promote> constant',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $this->visibility->value,
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}

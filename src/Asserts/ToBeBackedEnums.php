<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Identifier;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Enums\ScalarType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToBeBackedEnums implements ExprInterface
{
    public function __construct(
        private ?ScalarType $scalarType = null,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return sprintf(
            'to be backed enums type of <promote>%s</promote>',
            $this->scalarType->value ?? 'int or string',
        );
    }

    public function assert(ClassDescription $class): bool
    {
        if ($class->classType !== ClassType::Enum_) {
            return false;
        }

        if (!$class->scalarType instanceof Identifier) {
            return !$this->scalarType instanceof ScalarType;
        }

        return !$this->scalarType instanceof ScalarType
            || $this->scalarType->value === $class->scalarType->toLowerString();
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            sprintf(
                'Resource <promote>%s</promote> must be an enums type of <promote>%s</promote>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $this->scalarType->value ?? 'int or string',
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}

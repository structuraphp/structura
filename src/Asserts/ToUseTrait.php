<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

class ToUseTrait implements ExprInterface
{
    /** @var array<int,class-string> */
    private readonly array $names;

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function __construct(
        array|string $names,
        private readonly string $message,
    ) {
        $this->names = (array) $names;
    }

    public function __toString(): string
    {
        return \sprintf(
            'to use trait <promote>%s</promote>',
            implode(', ', $this->names),
        );
    }

    public function assert(ClassDescription $class): bool
    {
        return array_diff($this->names, $class->getTraitNames()) === [];
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must use traits <promote>%s</promote>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                implode(', ', $this->names),
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}

<?php

declare(strict_types=1);

namespace Structura\Asserts;

use Structura\Contracts\ExprInterface;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

class ToImplement implements ExprInterface
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
            'to implement <promote>%s</promote>',
            implode(', ', $this->names),
        );
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->isInterfaceable()
        && array_diff($this->names, $class->getInterfaceNames()) === [];
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must implement <promote>%s</promote>',
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

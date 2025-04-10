<?php

declare(strict_types=1);

namespace Structura\Asserts;

use PhpParser\Node\Name\FullyQualified;
use Structura\Contracts\ExprInterface;
use Structura\ValueObjects\ClassDescription;
use Structura\ValueObjects\ViolationValueObject;

class ToExtend implements ExprInterface
{
    public function __construct(
        public readonly string $name,
        private readonly string $message,
    ) {}

    public function __toString(): string
    {
        return \sprintf('to extend <promote>%s</promote>', $this->name);
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->isExtendable()
            && $class->extends instanceof FullyQualified
            && $this->name === $class->extends->toString();
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must extend by <promote>%s</promote>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                $this->name,
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}

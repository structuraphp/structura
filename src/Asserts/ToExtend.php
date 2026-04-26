<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToExtend implements ExprInterface
{
    /** @var array<int,class-string> */
    private array $names;

    /**
     * @param array<int,class-string>|class-string $names
     */
    public function __construct(
        array|string $names,
        private string $message = '',
    ) {
        $this->names = (array) $names;
    }

    public function __toString(): string
    {
        return \sprintf(
            'to extend <promote>%s</promote>',
            implode(', ', $this->names),
        );
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->isExtendable()
            && array_diff($this->names, $class->getExtendNames()) === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        $violations = array_diff($this->names, $class->getExtendNames());

        $results = [];
        foreach ($violations as $violation) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must extend by <promote>%s</promote>',
                    $class->getResourceName(),
                    $violation,
                ),
                $this::class,
                $class->lines,
                $class->getFileBasename(),
                $this->message,
            );
        }

        return $results;
    }
}

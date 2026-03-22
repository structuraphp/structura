<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Name;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToHaveOnlyAttribute implements ExprInterface
{
    /**
     * @param class-string $name
     */
    public function __construct(
        private string $name,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to have only attribute <promote>%s</promote>', $this->name);
    }

    public function assert(ClassDescription $class): bool
    {
        return $class->attrGroups !== []
            && $class->hasAttribute($this->name)
            && count($class->attrGroups) === 1;
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        /** @var array<array-key,Name> $violations */
        $violations = array_diff($class->getAttributeNames(), [$this->name]);

        if ($violations === []) {
            return [
                new ViolationValueObject(
                    \sprintf(
                        'Resource <promote>%s</promote> must have only attribute <promote>%s</promote>',
                        $class->getResourceName(),
                        $this->name,
                    ),
                    $this::class,
                    $class->lines,
                    $class->getFileBasename(),
                    $this->message,
                ),
            ];
        }

        $results = [];
        foreach ($violations as $violation) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must have only attribute <promote>%s</promote> but attribute <fire>%s</fire>',
                    $class->getResourceName(),
                    $this->name,
                    $violation->toString(),
                ),
                $this::class,
                $violation->getLine(),
                $class->getFileBasename(),
                $this->message,
            );
        }

        return $results;
    }
}

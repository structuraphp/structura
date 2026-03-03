<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Concerns\Arr;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\DependenciesType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class DependsOnlyOnAttribut implements ExprInterface
{
    use Arr;

    /**
     * @param array<int,class-string> $names
     * @param array<int,string> $patterns
     */
    public function __construct(
        private array $names,
        private array $patterns,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf(
            'depends only on attribut <promote>%s</promote>',
            $this->implodeMore(array_merge($this->names, $this->patterns)),
        );
    }

    public function assert(ClassDescription $class): bool
    {
        $dependencies = array_merge(
            $this->names,
            $class->getDependenciesByPatterns($this->patterns, DependenciesType::Attributes),
        );

        return array_diff($class->getAttributeNames(), array_unique($dependencies)) === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        $authorisedDependence = implode(', ', array_merge($this->names, $this->patterns));
        $dependencies = array_merge(
            $this->names,
            $class->getDependenciesByPatterns($this->patterns, DependenciesType::Attributes),
        );
        $violations = array_diff($class->getAttributeNames(), $dependencies);
        sort($violations);

        $results = [];
        foreach ($violations as $violation) {
            $results[] = new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must use attributes on these namespaces %s but use attributes <fire>%s</fire>',
                    $class->getResourceName(),
                    $authorisedDependence,
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

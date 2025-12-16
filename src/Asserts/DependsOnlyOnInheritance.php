<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Concerns\Arr;
use StructuraPhp\Structura\Contracts\ExceptInterface;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\DependenciesType;
use StructuraPhp\Structura\Exception\ExceptAssertionException;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class DependsOnlyOnInheritance implements ExprInterface, ExceptInterface
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
            'depends only on inheritance <promote>%s</promote>',
            $this->implodeMore(array_merge($this->names, $this->patterns)),
        );
    }

    public function assert(ClassDescription $class): bool
    {
        $dependencies = array_merge(
            $this->names,
            $class->getDependenciesByPatterns($this->patterns, DependenciesType::Extends),
        );

        return array_diff($class->getExtendNames(), array_unique($dependencies)) === [];
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        $authorisedDependence = array_merge($this->names, $this->patterns);
        $dependencies = array_merge(
            $this->names,
            $class->getDependenciesByPatterns($this->patterns, DependenciesType::Extends),
        );
        $violations = array_diff($class->getExtendNames(), $dependencies);
        sort($violations);

        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must inherit on these namespaces %s but inherits <fire>%s</fire>',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
                implode(', ', $authorisedDependence),
                implode(', ', $violations),
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }

    public function except(ExceptInterface $expr, ClassDescription $description): bool
    {
        if (!$expr instanceof $this) {
            throw new ExceptAssertionException($expr, $this);
        }

        $rule = new self(
            array_merge($this->names, $expr->names),
            array_merge($this->patterns, $expr->patterns),
        );

        return $rule->assert($description);
    }
}

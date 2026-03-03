<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Concerns\Arr;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotDependsOnFunction implements ExprScriptInterface
{
    use Arr;

    /**
     * @param array<int,string> $names
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
            'not depends on function <promote>%s</promote>',
            $this->implodeMore(array_merge($this->names, $this->patterns)),
        );
    }

    public function assert(ScriptDescription $description): bool
    {
        $dependencies = array_merge(
            $this->names,
            $description->getDependenciesFunctionByPatterns($this->patterns),
        );

        return array_intersect(
            $description->getFunctionDependencies(),
            array_unique($dependencies),
        ) === [];
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ScriptDescription $description): array
    {
        $authorisedDependence = array_merge($this->names, $this->patterns);
        $dependencies = array_merge(
            $this->names,
            $description->getDependenciesFunctionByPatterns($this->patterns),
        );
        $violations = array_intersect(
            $description->getFunctionDependencies(),
            array_unique($dependencies),
        );
        sort($violations);

        return [
            new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must not depends on functions %s but depends on <fire>%s</fire>',
                    $description->getResourceName(),
                    implode(', ', $authorisedDependence),
                    implode(', ', $violations),
                ),
                $this::class,
                $description instanceof ClassDescription
                    ? $description->lines
                    : 0,
                $description->getFileBasename(),
                $this->message,
            ),
        ];
    }
}

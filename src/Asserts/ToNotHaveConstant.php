<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Stmt\ClassConst;
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
        return \sprintf('to not have <promote>%s</promote> constant', $this->visibility->label());
    }

    public function assert(ClassDescription $class): bool
    {
        return match ($this->visibility) {
            VisibilityType::Public => !$class->hasPublicConstant(),
            VisibilityType::Protected => !$class->hasProtectedConstant(),
            VisibilityType::Private => !$class->hasPrivateConstant(),
        };
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ClassDescription $class): array
    {
        $results = [];
        foreach ($class->constants as $classConst) {
            if (!$this->hasVisibility($classConst)) {
                continue;
            }

            foreach ($classConst->consts as $const) {
                $results[] = new ViolationValueObject(
                    \sprintf(
                        'Resource <promote>%s</promote> must not have <promote>%s</promote> constant <fire>%s</fire>',
                        $class->getResourceName(),
                        $this->visibility->label(),
                        $const->name->toString(),
                    ),
                    $this::class,
                    $const->getLine(),
                    $class->getFileBasename(),
                    $this->message,
                );
            }
        }

        return $results;
    }

    private function hasVisibility(ClassConst $classConst): bool
    {
        return match ($this->visibility) {
            VisibilityType::Public => $classConst->isPublic(),
            VisibilityType::Protected => $classConst->isProtected(),
            VisibilityType::Private => $classConst->isPrivate(),
        };
    }
}

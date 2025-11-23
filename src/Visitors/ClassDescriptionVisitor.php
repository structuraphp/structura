<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Visitors;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeVisitorAbstract;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

final class ClassDescriptionVisitor extends NodeVisitorAbstract
{
    private ?Declare_ $declare = null;

    private ?ClassDescription $class;

    public function getClass(): ?ClassDescription
    {
        return $this->class;
    }

    /**
     * @param array<Node> $nodes
     */
    public function beforeTraverse(array $nodes): null
    {
        $this->class = null;

        return null;
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Declare_) {
            $this->declare = $node;
        }

        if ($node instanceof ClassLike && !$this->class instanceof ClassDescription) {
            $this->class = new ClassDescription(
                namespace: $node->namespacedName?->toString() ?? '',
                declare: $this->declare,
                name: $node->name?->name,
                attrGroups: $node->attrGroups,
                lines: $node->getLine(),
                scalarType: $node instanceof Enum_
                    ? $node->scalarType
                    : null,
                interfaces: $node instanceof Class_ || $node instanceof Enum_
                    ? $node->implements
                    : null,
                extends: $node instanceof Class_ || $node instanceof Interface_
                    ? $node->extends
                    : null,
                traits: $node->getTraitUses(),
                flags: $node instanceof Class_
                    ? $node->flags
                    : null,
                classType: $this->getClassType($node),
                methods: $node->getMethods(),
            );
        }

        return null;
    }

    private function getClassType(ClassLike $node): ClassType
    {
        if ($node instanceof Class_) {
            return $node->isAnonymous()
                ? ClassType::AnonymousClass_
                : ClassType::Class_;
        }

        if ($node instanceof Interface_) {
            return ClassType::Interface_;
        }

        if ($node instanceof Trait_) {
            return ClassType::Trait_;
        }

        if ($node instanceof Enum_) {
            return ClassType::Enum_;
        }

        throw new InvalidArgumentException();
    }
}

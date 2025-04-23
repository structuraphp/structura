<?php

declare(strict_types=1);

namespace Structura\Visitors;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

class NamespaceVisitor extends NodeVisitorAbstract
{
    private const TYPES = [
        'bool' => 1,
        'true' => 1,
        'false' => 1,
        'callable' => 1,
        'int' => 1,
        'float' => 1,
        'string' => 1,
        'array' => 1,
        'object' => 1,
        'null' => 1,
        'mixed' => 1,
    ];

    /** @var array<string,int> */
    private array $dependencies = [];

    /** @var array<string,int> */
    private array $namespace = [];

    /**
     * @return  array<string,int>
     */
    public function getDependencies(): array
    {
        $dependencies = array_keys($this->dependencies);

        foreach (array_keys($this->namespace) as $namespace) {
            foreach ($dependencies as $dependency) {
                if (
                    str_starts_with($dependency, $namespace . '\\')
                    && !\in_array($namespace, $dependencies, true)
                ) {
                    unset($this->namespace[$namespace]);
                    break;
                }
            }
        }

        return $this->namespace + $this->dependencies;
    }

    /**
     * @param array<Node> $nodes
     */
    public function beforeTraverse(array $nodes): null
    {
        $this->dependencies = [];

        return null;
    }

    public function enterNode(Node $node): null
    {
        match (true) {
            $node instanceof GroupUse => $this->addGroupUseDependency($node),
            $node instanceof Use_ => $this->addUseDependency($node),
            $node instanceof Class_ => $this->addClassDependency($node),
            $node instanceof Enum_ => $this->addEnumDependency($node),
            $node instanceof Interface_ => $this->addInterfaceDependency($node),
            $node instanceof TraitUse => $this->addTraitDependency($node),
            $node instanceof Attribute => $this
                ->addDependency($node->name->getLine(), $node->name->toString()),
            $node instanceof New_
            || $node instanceof Instanceof_
            || $node instanceof ClassConstFetch => $this->addStaticDependency($node),
            $node instanceof Param
            || $node instanceof Property => $this->addParamDependency($node),
            $node instanceof ClassMethod
            || $node instanceof ArrowFunction
            || $node instanceof Closure => $this->addMethodDependency($node),
            $node instanceof Catch_ => $this->addCatchDependency($node),
            default => null,
        };

        return null;
    }

    private function addGroupUseDependency(GroupUse $node): void
    {
        if ($node->type !== Use_::TYPE_UNKNOWN) {
            return;
        }

        foreach ($node->uses as $use) {
            $this->addNamespace(
                $use->name->getLine(),
                $node->prefix->toString()
                . "\\"
                . $use->name->toString(),
            );
        }
    }

    private function addUseDependency(Use_ $node): void
    {
        if ($node->type !== Use_::TYPE_NORMAL) {
            return;
        }

        foreach ($node->uses as $use) {
            $this->addNamespace($use->name->getLine(), $use->name->toString());
        }
    }

    private function addTraitDependency(TraitUse $node): void
    {
        foreach ($node->traits as $trait) {
            $this->addDependency($trait->getLine(), $trait->toString());
        }
    }

    private function addStaticDependency(
        ClassConstFetch|Instanceof_|New_ $node,
    ): void {
        $class = $node->class;
        if (!$class instanceof Name || $class->isSpecialClassName()) {
            return;
        }

        $this->addDependency($class->getLine(), $class->toString());
    }

    private function addClassDependency(Class_ $node): void
    {
        if ($node->extends instanceof Name) {
            $this->addDependency($node->extends->getLine(), $node->extends->toString());
        }

        foreach ($node->implements as $interface) {
            $this->addDependency($interface->getLine(), $interface->toString());
        }
    }

    private function addEnumDependency(Enum_ $node): void
    {
        foreach ($node->implements as $interface) {
            $this->addDependency($interface->getLine(), $interface->toString());
        }
    }

    private function addInterfaceDependency(Interface_ $node): void
    {
        foreach ($node->extends as $extend) {
            $this->addDependency($extend->getLine(), $extend->toString());
        }
    }

    private function addMethodDependency(ClassMethod|ArrowFunction|Closure $node): void
    {
        $returnType = $node->returnType;

        if ($returnType instanceof FullyQualified) {
            $this->addDependency($returnType->getLine(), $returnType->toString());
        }
    }

    private function addParamDependency(Property|Param $node): void
    {
        $type = $node->type;

        if ($type instanceof NullableType) {
            $type = $type->type;
        }

        if (!$type instanceof Name) {
            return;
        }

        if ($type->isSpecialClassName() || $this->isBuiltInType($type->toString())) {
            return;
        }

        $this->addDependency($type->getLine(), $type->toString());
    }

    private function addCatchDependency(Catch_ $node): void
    {
        foreach ($node->types as $type) {
            $this->addDependency($type->getLine(), $type->toString());
        }
    }

    private function isBuiltInType(string $typeName): bool
    {
        return isset(self::TYPES[$typeName]);
    }

    private function addDependency(int $line, string $dependency): void
    {
        $this->dependencies[$dependency] = $line;
    }

    private function addNamespace(int $line, string $dependency): void
    {
        $this->namespace[$dependency] = $line;
    }
}

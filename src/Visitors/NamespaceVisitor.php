<?php

declare(strict_types=1);

namespace Structura\Visitors;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\UseUse;
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

    /** @var array<int,string> */
    private array $dependencies = [];

    /**
     * @return  array<int,string>
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
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
        if ($node instanceof Class_) {
            $this->addClassDependency($node);
        }

        if ($node instanceof UseUse) {
            $this->dependencies[$node->name->getLine()] = $node->name->toString();
        }

        if (
            $node instanceof StaticCall
            || $node instanceof ClassConstFetch
            || $node instanceof Instanceof_
            || $node instanceof New_
        ) {
            $class = $node->class;
            if (!$class instanceof Name || $class->isSpecialClassName()) {
                return null;
            }

            $this->dependencies[$class->getLine()] = $class->toString();
        }

        if ($node instanceof Param || $node instanceof Property) {
            $this->addParamDependency($node);
        }

        if ($node instanceof ClassMethod) {
            $this->addMethodDependency($node);
        }

        return null;
    }

    private function addClassDependency(Class_ $node): void
    {
        foreach ($node->implements as $interface) {
            $this->dependencies[$interface->getLine()] = $interface->toString();
        }

        if ($node->extends instanceof Name) {
            $this->dependencies[$node->extends->getLine()] = $node->extends->toString();
        }
    }

    private function addMethodDependency(ClassMethod $node): void
    {
        $returnType = $node->returnType;

        if ($returnType instanceof FullyQualified) {
            $this->dependencies[$returnType->getLine()] = $returnType->toString();
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

        $this->dependencies[$type->getLine()] = $type->toString();
    }

    private function isBuiltInType(string $typeName): bool
    {
        return isset(self::TYPES[$typeName]);
    }
}

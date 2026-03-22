<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Visitors;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeVisitorAbstract;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

final class ClassDescriptionVisitor extends NodeVisitorAbstract
{
    protected ?Return_ $rootReturn = null;

    private ?string $namespace = null;

    private ?Declare_ $declare = null;

    /** @var array<int,Include_> */
    private array $includes = [];

    private ?string $name = null;

    /** @var array<array-key, AttributeGroup> */
    private array $attrGroups = [];

    private int $lines = 0;

    private ?Identifier $scalarType = null;

    /** @var array<array-key,Name> */
    private ?array $interfaces = null;

    /** @var null|array<Name>|Name */
    private array|Name|null $extends = null;

    /** @var array<TraitUse> */
    private array $traits = [];

    private ?int $flags = null;

    private ClassType $classType = ClassType::Class_;

    /** @var null|array<ClassMethod> */
    private ?array $methods = null;

    /** @var array<ClassConst> */
    private array $constants = [];

    private ?ClassDescription $class;

    private int $classDeep = 0;

    /** @var array<int,Class_> */
    private array $anonymousClasses = [];

    private bool $inRootReturn = false;

    public function getClass(): ?ClassDescription
    {
        return $this->class;
    }

    /**
     * @param array<Node> $nodes
     */
    public function beforeTraverse(array $nodes): null
    {
        $this->attrGroups = [];
        $this->class = null;
        $this->classDeep = 0;
        $this->classType = ClassType::Class_;
        $this->constants = [];
        $this->declare = null;
        $this->extends = null;
        $this->flags = null;
        $this->includes = [];
        $this->inRootReturn = false;
        $this->interfaces = null;
        $this->lines = 0;
        $this->methods = null;
        $this->name = null;
        $this->scalarType = null;
        $this->traits = [];
        $this->rootReturn = null;

        return null;
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Declare_) {
            $this->declare = $node;
        }

        if ($node instanceof Include_) {
            $this->includes[] = $node;
        }

        if ($node instanceof Return_ && $this->classDeep === 0) {
            $this->inRootReturn = true;
            $this->rootReturn = $node;
        }

        if ($node instanceof Class_ && $node->isAnonymous() && $this->classDeep > 0) {
            $this->anonymousClasses[] = $node;
            $this->classDeep++;
        }

        if ($node instanceof ClassLike && $this->classDeep === 0) {
            $this->classDeep++;

            $this->namespace = $node->namespacedName?->toString() ?? '';
            $this->name = $node->name?->name;
            $this->attrGroups = $node->attrGroups;
            $this->lines = $node->getLine();
            $this->scalarType = $node instanceof Enum_
                ? $node->scalarType
                : null;
            $this->interfaces = $node instanceof Class_ || $node instanceof Enum_
                ? $node->implements
                : null;
            $this->extends = $node instanceof Class_ || $node instanceof Interface_
                ? $node->extends
                : null;
            $this->traits = $node->getTraitUses();
            $this->flags = $node instanceof Class_
                ? $node->flags
                : null;
            $this->classType = $this->getClassType($node);
            $this->methods = $node->getMethods();

            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof ClassConst) {
                    $this->constants[] = $stmt;
                }
            }
        }

        return null;
    }

    public function afterTraverse(array $nodes): null
    {
        if (!$this->class instanceof ClassDescription) {
            $this->class = new ClassDescription(
                namespace: $this->namespace,
                declare: $this->declare,
                includes: $this->includes,
                anonymousClasses: $this->anonymousClasses,
                rootReturn: $this->rootReturn,
                name: $this->name,
                attrGroups: $this->attrGroups,
                lines: $this->lines,
                scalarType: $this->scalarType,
                interfaces: $this->interfaces,
                extends: $this->extends,
                traits: $this->traits,
                flags: $this->flags,
                classType: $this->classType,
                methods: $this->methods,
                constants: $this->constants,
            );
        }

        return null;
    }

    private function getClassType(ClassLike $node): ClassType
    {
        if ($node instanceof Class_) {
            return $node->isAnonymous() && $this->inRootReturn
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

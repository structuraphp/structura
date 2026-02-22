<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Visitors;

use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;

final class ScriptDescriptionVisitor extends NodeVisitorAbstract
{
    private ?Declare_ $declare = null;

    private ?Namespace_ $namespace = null;

    private ?ScriptDescription $script;

    /** @var array<int,Include_> */
    private array $includes = [];

    public function getScript(): ?ScriptDescription
    {
        $script = $this->script;
        $this->script = null;

        return $script;
    }

    /**
     * @param array<Node> $nodes
     */
    public function beforeTraverse(array $nodes): null
    {
        $this->script = null;
        $this->declare = null;
        $this->namespace = null;
        $this->includes = [];

        return null;
    }

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Declare_) {
            $this->declare = $node;
        }

        if ($node instanceof Namespace_) {
            $this->namespace = $node;
        }

        if ($node instanceof Include_) {
            $this->includes[] = $node;
        }

        return null;
    }

    public function afterTraverse(array $nodes): null
    {
        if (!$this->script instanceof ScriptDescription) {
            $this->script = new ScriptDescription(
                namespace: $this->namespace?->name?->toString(),
                declare: $this->declare,
                includes: $this->includes,
            );
        }

        return null;
    }
}

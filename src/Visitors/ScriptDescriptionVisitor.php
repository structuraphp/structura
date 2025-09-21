<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Visitors;

use PhpParser\Node;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;

final class ScriptDescriptionVisitor extends NodeVisitorAbstract
{
    private ?Declare_ $declare = null;

    private ?Namespace_ $namespace = null;

    private ?ScriptDescription $script;

    public function getScript(): ?ScriptDescription
    {
        return $this->script;
    }

    /**
     * @param array<Node> $nodes
     */
    public function beforeTraverse(array $nodes): null
    {
        $this->script = null;

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

        if (!$this->script instanceof ScriptDescription) {
            $this->script = new ScriptDescription(
                namespace: $this->namespace?->name?->toString() ?? '',
                declare: $this->declare,
            );
        }

        return null;
    }
}

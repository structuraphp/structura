<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Helper;

use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;

trait ParserHelper
{
    /**
     * @template TVisitor of NodeVisitor
     *
     * @param TVisitor $visitor
     */
    public function traverse(NodeVisitor $visitor, string $raw): NodeVisitor
    {
        $parser = (new ParserFactory())->createForHostVersion();
        $nodeTraverse = new NodeTraverser(new NameResolver(), $visitor);

        /** @var array<int,Stmt> $ast */
        $ast = $parser->parse($raw);

        $nodeTraverse->traverse($ast);

        return $visitor;
    }
}

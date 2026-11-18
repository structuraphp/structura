<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Visitors;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

final class FunctionVisitor extends NodeVisitorAbstract
{
    /** @var array<int,array{dependency: string, line: int}> */
    private array $dependencies = [];

    /** @var array<int,array{dependency: string, line: int}> */
    private array $namespace = [];

    /**
     * @return array<string,int>
     */
    public function getDependencies(): array
    {
        /** @var array<int,string> $dependencies */
        $dependencies = array_column($this->dependencies, 'dependency');

        /** @var array<int,string> $namespaces */
        $namespaces = array_column($this->namespace, 'dependency');

        foreach ($namespaces as $namespaceKey => $namespace) {
            $namespaceLast = strrchr($namespace, '\\');

            if ($namespaceLast !== false) {
                $namespaceLast = substr($namespaceLast, 1);

                // Resolve namespace
                foreach ($dependencies as $key => $dependency) {
                    if (str_starts_with($dependency, $namespaceLast)) {
                        $dependencies[$key] = strstr($namespace, '\\', true)
                            . '\\'
                            . $dependency;
                    }
                }
            }

            /*
             * Removes shadows-dependencies, example:
             *
             * use function Dependency\Shadow;
             * Shadow\foo();
             *
             * ['Dependency\Shadow\foo()'] !== ['Dependency\Shadow', 'Shadow\foo()']
             */
            foreach ($dependencies as $dependency) {
                if (
                    str_starts_with($dependency, $namespace . '\\')
                    && !\in_array($namespace, $dependencies, true)
                ) {
                    unset($namespaces[$namespaceKey]);
                }
            }

            if (\in_array($namespace, $dependencies, true)) {
                unset($namespaces[$namespaceKey]);
            }
        }

        // first occurrence line, use statement included
        $lines = [];
        foreach ($this->namespace as $use) {
            $lines[$use['dependency']] ??= $use['line'];
        }

        foreach ($dependencies as $key => $dependency) {
            $lines[$dependency] ??= $this->dependencies[$key]['line'];
        }

        $output = [];
        foreach (array_merge($namespaces, $dependencies) as $dependency) {
            $output[$dependency] = $lines[$dependency];
        }

        // clean dependencies
        $this->namespace = [];
        $this->dependencies = [];

        return $output;
    }

    /**
     * @param array<Node> $nodes
     */
    public function beforeTraverse(array $nodes): null
    {
        $this->namespace = [];
        $this->dependencies = [];

        return null;
    }

    public function enterNode(Node $node): null
    {
        match (true) {
            $node instanceof FuncCall => $this->addFuncCallDependencies($node),
            $node instanceof Use_ => $this->addUseDependencies($node),
            default => null,
        };

        return null;
    }

    private function addFuncCallDependencies(FuncCall $node): void
    {
        if (!$node->name instanceof Name) {
            return;
        }

        $this->addDependency($node->getLine(), $node->name->toString());
    }

    private function addUseDependencies(Use_ $node): void
    {
        if ($node->type !== Use_::TYPE_FUNCTION) {
            return;
        }

        foreach ($node->uses as $use) {
            $this->addNamespace($use->name->getLine(), $use->name->toString());
        }
    }

    private function addDependency(int $line, string $dependency): void
    {
        $this->dependencies[] = ['line' => $line, 'dependency' => $dependency];
    }

    private function addNamespace(int $line, string $dependency): void
    {
        $this->namespace[] = ['line' => $line, 'dependency' => $dependency];
    }
}

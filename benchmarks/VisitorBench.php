<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks;

use Generator;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use StructuraPhp\Structura\Visitors\ClassDescriptionVisitor;
use StructuraPhp\Structura\Visitors\DependenciesVisitor;
use StructuraPhp\Structura\Visitors\FunctionVisitor;
use StructuraPhp\Structura\Visitors\ScriptDescriptionVisitor;

/**
 * Measures how ClassDescription and ScriptDescription are built by the visitors.
 *
 * The corpus is parsed into raw ASTs in the before method, so the lexer and the
 * parser are out of the measurement. Every variant embeds a NameResolver to stay
 * comparable with ParseService; it rewrites the AST in place, and the AST is
 * rebuilt on the next iteration.
 */
#[BeforeMethods('setUp')]
#[Warmup(1)]
#[Revs(10)]
#[Iterations(5)]
final class VisitorBench
{
    /** @var array<int, array<int, Stmt>> */
    private array $asts = [];

    private NodeTraverser $nodeTraverser;

    private ClassDescriptionVisitor $classDescriptionVisitor;

    private ScriptDescriptionVisitor $scriptDescriptionVisitor;

    private DependenciesVisitor $dependenciesVisitor;

    private FunctionVisitor $functionVisitor;

    private string $variant;

    /**
     * @param array{visitors: string} $params
     */
    public function setUp(array $params): void
    {
        $this->variant = $params['visitors'];

        $parser = (new ParserFactory())->createForHostVersion();

        foreach (Corpus::finder() as $file) {
            /** @var array<int, Stmt> $ast */
            $ast = $parser->parse($file->getContents());
            $this->asts[] = $ast;
        }

        $this->classDescriptionVisitor = new ClassDescriptionVisitor();
        $this->scriptDescriptionVisitor = new ScriptDescriptionVisitor();
        $this->dependenciesVisitor = new DependenciesVisitor();
        $this->functionVisitor = new FunctionVisitor();

        $this->nodeTraverser = new NodeTraverser(
            new NameResolver(),
            ...$this->visitors($params['visitors']),
        );
    }

    #[ParamProviders('provideVisitors')]
    public function benchTraverse(): void
    {
        foreach ($this->asts as $ast) {
            $this->nodeTraverser->traverse($ast);

            $this->readDescription();
        }
    }

    /**
     * @return Generator<string, array{visitors: string}>
     */
    public static function provideVisitors(): Generator
    {
        foreach (['classDescription', 'scriptDescription', 'dependencies', 'functions', 'all'] as $name) {
            yield $name => ['visitors' => $name];
        }
    }

    /**
     * Reads what the traversal produced, as ParseService::parseRaw() does.
     */
    private function readDescription(): void
    {
        match ($this->variant) {
            'classDescription' => $this->classDescriptionVisitor->getClass(),
            'scriptDescription' => $this->scriptDescriptionVisitor->getScript(),
            'dependencies' => $this->dependenciesVisitor->getDependencies()
                + $this->dependenciesVisitor->getDocBlockDependencies(),
            'functions' => $this->functionVisitor->getDependencies(),
            default => $this->classDescriptionVisitor
                ->getClass()
                ?->setClassDependencies(array_keys($this->dependenciesVisitor->getDependencies()))
                ->setDocBlockDependencies(array_keys($this->dependenciesVisitor->getDocBlockDependencies()))
                ->setFunctionDependencies($this->functionVisitor->getDependencies()),
        };
    }

    /**
     * @return array<int, NodeVisitor>
     */
    private function visitors(string $name): array
    {
        return match ($name) {
            'classDescription' => [$this->classDescriptionVisitor],
            'scriptDescription' => [$this->scriptDescriptionVisitor],
            'dependencies' => [$this->dependenciesVisitor],
            'functions' => [$this->functionVisitor],
            // same pipeline as ParseService
            default => [
                $this->scriptDescriptionVisitor,
                $this->classDescriptionVisitor,
                $this->dependenciesVisitor,
                $this->functionVisitor,
            ],
        };
    }
}

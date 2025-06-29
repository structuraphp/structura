<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use Error;
use Generator;
use InvalidArgumentException;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\Visitors\ClassDescriptionVisitor;
use StructuraPhp\Structura\Visitors\DependenciesVisitor;
use StructuraPhp\Structura\Visitors\FunctionVisitor;
use Symfony\Component\Finder\Finder;

final readonly class ParseService
{
    private Parser $parser;

    private NodeTraverser $nodeTraverser;

    private ClassDescriptionVisitor $classDescriptionVisitor;

    private DependenciesVisitor $dependenciesVisitor;

    private FunctionVisitor $functionVisitor;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->createForHostVersion();

        $this->classDescriptionVisitor = new ClassDescriptionVisitor();
        $this->dependenciesVisitor = new DependenciesVisitor();
        $this->functionVisitor = new FunctionVisitor();

        $this->nodeTraverser = new NodeTraverser(
            new NameResolver(),
            $this->classDescriptionVisitor,
            $this->dependenciesVisitor,
            $this->functionVisitor,
        );
    }

    /**
     * @return Generator<ClassDescription>
     */
    public function parse(Finder $finder): Generator
    {
        foreach ($finder as $file) {
            yield from $this->parseRaw($file->getContents(), $file->getRealPath());
        }
    }

    /**
     * @return Generator<ClassDescription>
     */
    public function parseRaw(string $raw, ?string $pathname = null): Generator
    {
        try {
            /** @var array<int,Stmt> $ast */
            $ast = $this->parser->parse($raw);

            $this->nodeTraverser->traverse($ast);

            $class = $this->classDescriptionVisitor->getClass()
                ?? throw new InvalidArgumentException();
        } catch (Error|InvalidArgumentException $e) {
            echo \sprintf('Parse error: %s%s', $e->getMessage(), PHP_EOL);

            return null;
        }

        yield $class
            ->setClassDependencies(
                array_keys($this->dependenciesVisitor->getDependencies()),
            )
            ->setFunctionDependencies(
                $this->functionVisitor->getDependencies(),
            )
            ->setFilePathname($pathname);
    }
}

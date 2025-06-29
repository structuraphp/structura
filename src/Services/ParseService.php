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
use StructuraPhp\Structura\Visitors\DependanciesVisitor;
use Symfony\Component\Finder\Finder;

final readonly class ParseService
{
    private Parser $parser;

    private NodeTraverser $nodeTraverser;

    private ClassDescriptionVisitor $classDescriptionVisitor;

    private DependanciesVisitor $namespaceVisitor;

    public function __construct()
    {
        $this->parser = (new ParserFactory())->createForHostVersion();

        $this->classDescriptionVisitor = new ClassDescriptionVisitor();
        $this->namespaceVisitor = new DependanciesVisitor();

        $this->nodeTraverser = new NodeTraverser(
            new NameResolver(),
            $this->classDescriptionVisitor,
            $this->namespaceVisitor,
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
            ->setDependencies(
                array_keys($this->namespaceVisitor->getDependencies()),
            )
            ->setFilePathname($pathname);
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use Exception;
use Generator;
use InvalidArgumentException;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use StructuraPhp\Structura\Enums\DescriptorType;
use StructuraPhp\Structura\Exception\Console\NoticeException;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\Visitors\ClassDescriptionVisitor;
use StructuraPhp\Structura\Visitors\DependenciesVisitor;
use StructuraPhp\Structura\Visitors\FunctionVisitor;
use StructuraPhp\Structura\Visitors\ScriptDescriptionVisitor;
use Symfony\Component\Finder\Finder;

final readonly class ParseService
{
    private Parser $parser;

    private NodeTraverser $nodeTraverser;

    private ScriptDescriptionVisitor $scriptDescriptionVisitor;

    private ClassDescriptionVisitor $classDescriptionVisitor;

    private DependenciesVisitor $dependenciesVisitor;

    private FunctionVisitor $functionVisitor;

    public function __construct(
        private DescriptorType $descriptorType = DescriptorType::Script,
    ) {
        $this->parser = (new ParserFactory())->createForHostVersion();

        $this->scriptDescriptionVisitor = new ScriptDescriptionVisitor();
        $this->classDescriptionVisitor = new ClassDescriptionVisitor();
        $this->dependenciesVisitor = new DependenciesVisitor();
        $this->functionVisitor = new FunctionVisitor();

        $this->nodeTraverser = new NodeTraverser(
            new NameResolver(),
            $this->scriptDescriptionVisitor,
            $this->classDescriptionVisitor,
            $this->dependenciesVisitor,
            $this->functionVisitor,
        );
    }

    /**
     * @return Generator<ClassDescription|ScriptDescription>
     */
    public function parse(Finder $finder): Generator
    {
        foreach ($finder as $file) {
            yield from $this->parseRaw($file->getContents(), $file->getRealPath());
        }
    }

    /**
     * @return Generator<ClassDescription|ScriptDescription>
     */
    public function parseRaw(string $raw, string $pathname): Generator
    {
        try {
            /** @var array<int,Stmt> $ast */
            $ast = $this->parser->parse($raw);

            $this->nodeTraverser->traverse($ast);

            $script = $this->descriptorType === DescriptorType::ClassLike
                ? $this->classDescriptionVisitor->getClass()
                : $this->scriptDescriptionVisitor->getScript();

            if (!$script instanceof ScriptDescription) {
                throw new InvalidArgumentException(
                    $this->descriptorType === DescriptorType::ClassLike
                        ? 'class expected but script found'
                        : 'script expected but class found',
                );
            }
        } catch (InvalidArgumentException $e) {
            throw new NoticeException(
                \sprintf('<orange>Parse error, %s</orange> at %s', $e->getMessage(), $pathname),
            );
        } catch (Exception $e) {
            throw new NoticeException(
                \sprintf('<orange>%s</orange> at %s', $e->getMessage(), $pathname),
            );
        }

        yield $script
            ->setClassDependencies(
                array_keys($this->dependenciesVisitor->getDependencies()),
            )
            ->setFunctionDependencies(
                $this->functionVisitor->getDependencies(),
            )
            ->setFilePathname($pathname);
    }
}

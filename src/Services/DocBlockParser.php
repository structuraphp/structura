<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use PHPStan\PhpDocParser\Ast\PhpDoc\ExtendsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ImplementsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\MixinTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayShapeNode;
use PHPStan\PhpDocParser\Ast\Type\ArrayTypeNode;
use PHPStan\PhpDocParser\Ast\Type\CallableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use PHPStan\PhpDocParser\Ast\Type\NullableTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use PHPStan\PhpDocParser\ParserConfig;

final readonly class DocBlockParser
{
    /** @var array<string, true> */
    private const BUILTIN_TYPES = [
        'int' => true,
        'integer' => true,
        'float' => true,
        'double' => true,
        'string' => true,
        'bool' => true,
        'boolean' => true,
        'null' => true,
        'void' => true,
        'never' => true,
        'array' => true,
        'list' => true,
        'object' => true,
        'callable' => true,
        'iterable' => true,
        'resource' => true,
        'mixed' => true,
        'true' => true,
        'false' => true,
        'self' => true,
        'static' => true,
        'parent' => true,
        '$this' => true,
        'class-string' => true,
        'scalar' => true,
        'numeric' => true,
        'positive-int' => true,
        'negative-int' => true,
        'non-empty-string' => true,
        'non-empty-array' => true,
        'non-empty-list' => true,
        'numeric-string' => true,
        'array-key' => true,
        'key-of' => true,
        'value-of' => true,
        'int-mask' => true,
        'int-mask-of' => true,
        'literal-string' => true,
    ];

    private PhpDocParser $phpDocParser;

    private Lexer $lexer;

    public function __construct()
    {
        $config = new ParserConfig([]);
        $this->lexer = new Lexer($config);
        $constExprParser = new ConstExprParser($config);
        $typeParser = new TypeParser($config, $constExprParser);
        $this->phpDocParser = new PhpDocParser($config, $typeParser, $constExprParser);
    }

    /**
     * Parse a docblock and extract all class names referenced in type annotations.
     *
     * @return array<int, string>
     */
    public function parse(string $docBlock): array
    {
        $tokens = new TokenIterator($this->lexer->tokenize($docBlock));
        $phpDocNode = $this->phpDocParser->parse($tokens);

        return $this->extractClassNames($phpDocNode);
    }

    /**
     * @return array<int, string>
     */
    private function extractClassNames(PhpDocNode $phpDocNode): array
    {
        $classNames = [];

        foreach ($phpDocNode->getTags() as $tag) {
            $types = $this->getTypesFromTag($tag);

            foreach ($types as $type) {
                $this->extractFromType($type, $classNames);
            }
        }

        return array_values(array_unique($classNames));
    }

    /**
     * @return array<int, TypeNode>
     */
    private function getTypesFromTag(PhpDocTagNode $tag): array
    {
        $value = $tag->value;

        return match (true) {
            $value instanceof ParamTagValueNode => [$value->type],
            $value instanceof ReturnTagValueNode => [$value->type],
            $value instanceof VarTagValueNode => [$value->type],
            $value instanceof ThrowsTagValueNode => [$value->type],
            $value instanceof PropertyTagValueNode => [$value->type],
            $value instanceof ExtendsTagValueNode => [$value->type],
            $value instanceof ImplementsTagValueNode => [$value->type],
            $value instanceof MixinTagValueNode => [$value->type],
            default => [],
        };
    }

    /**
     * @param array<int, string> $classNames
     */
    private function extractFromType(TypeNode $type, array &$classNames): void
    {
        match (true) {
            $type instanceof IdentifierTypeNode => $this->addIfClassName($type->name, $classNames),
            $type instanceof UnionTypeNode => $this->extractFromCompoundType($type->types, $classNames),
            $type instanceof IntersectionTypeNode => $this->extractFromCompoundType($type->types, $classNames),
            $type instanceof GenericTypeNode => $this->extractFromGenericType($type, $classNames),
            $type instanceof ArrayTypeNode => $this->extractFromType($type->type, $classNames),
            $type instanceof NullableTypeNode => $this->extractFromType($type->type, $classNames),
            $type instanceof CallableTypeNode => $this->extractFromCallableType($type, $classNames),
            $type instanceof ArrayShapeNode => $this->extractFromArrayShapeNode($type, $classNames),
            default => null,
        };
    }

    /**
     * @param array<TypeNode> $types
     * @param array<int, string> $classNames
     */
    private function extractFromCompoundType(array $types, array &$classNames): void
    {
        foreach ($types as $type) {
            $this->extractFromType($type, $classNames);
        }
    }

    /**
     * @param array<int, string> $classNames
     */
    private function extractFromGenericType(GenericTypeNode $type, array &$classNames): void
    {
        $this->addIfClassName($type->type->name, $classNames);

        foreach ($type->genericTypes as $genericType) {
            $this->extractFromType($genericType, $classNames);
        }
    }

    /**
     * @param array<int, string> $classNames
     */
    private function extractFromCallableType(CallableTypeNode $type, array &$classNames): void
    {
        $this->extractFromType($type->returnType, $classNames);

        foreach ($type->parameters as $parameter) {
            $this->extractFromType($parameter->type, $classNames);
        }
    }

    /**
     * @param array<int, string> $classNames
     */
    private function extractFromArrayShapeNode(ArrayShapeNode $type, array &$classNames): void
    {
        foreach ($type->items as $item) {
            $this->extractFromType($item->valueType, $classNames);
        }
    }

    /**
     * @param array<int, string> $classNames
     */
    private function addIfClassName(string $name, array &$classNames): void
    {
        if (!isset(self::BUILTIN_TYPES[strtolower($name)])) {
            $classNames[] = $name;
        }
    }
}

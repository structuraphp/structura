<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Enums\DescriptorType;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use Symfony\Component\Finder\Finder;

final readonly class RuleValuesObject
{
    /**
     * @param array<string, string> $raws
     * @param array<string, string> $pathResolvers
     */
    public function __construct(
        public array $raws,
        public ?Finder $finder,
        public ?AbstractExpr $that,
        public ?Except $except,
        public AbstractExpr $should,
        public array $pathResolvers = [],
    ) {}

    public function getDescriptorType(): DescriptorType
    {
        return $this->should instanceof Expr
            ? DescriptorType::ClassLike
            : DescriptorType::Script;
    }
}

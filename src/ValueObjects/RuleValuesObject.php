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
    public function __construct(
        public string $raw,
        public ?Finder $finder,
        public ?AbstractExpr $that,
        public ?Except $except,
        public AbstractExpr $should,
    ) {}

    public function getDescriptorType(): DescriptorType
    {
        return $this->should instanceof Expr
            ? DescriptorType::ClassLike
            : DescriptorType::Script;
    }
}

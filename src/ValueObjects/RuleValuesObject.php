<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\ValueObjects;

use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use Symfony\Component\Finder\Finder;

final readonly class RuleValuesObject
{
    public function __construct(
        public string $raw,
        public ?Finder $finder,
        public ?Expr $thats,
        public ?Except $except,
        public Expr $shoulds,
    ) {}
}

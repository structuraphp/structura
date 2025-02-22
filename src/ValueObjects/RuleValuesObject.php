<?php

declare(strict_types=1);

namespace Structura\ValueObjects;

use Structura\Except;
use Structura\Expr;
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

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Contracts;

use IteratorAggregate;
use Traversable;

/**
 * @template T of ExprInterface|mixed
 *
 * @extends IteratorAggregate<int, ExprIteratorAggregate|T>
 */
interface ExprIteratorAggregate extends IteratorAggregate
{
    public function addExpr(ExprInterface $expr): static;

    /**
     * @return Traversable<int, T>
     */
    public function getIterator(): Traversable;
}

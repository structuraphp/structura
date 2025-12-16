<?php

declare(strict_types=1);

namespace StructuraPhp\Structura;

use StructuraPhp\Structura\Concerns\Expr\DependencyAssert;
use StructuraPhp\Structura\Concerns\Expr\MethodAssert;
use StructuraPhp\Structura\Concerns\Expr\NameAssert;
use StructuraPhp\Structura\Concerns\Expr\OtherAssert;
use StructuraPhp\Structura\Concerns\Expr\RelationAssert;
use StructuraPhp\Structura\Concerns\Expr\TypeAssert;
use StructuraPhp\Structura\Concerns\ExprScript\DeclareAssert as ScriptDeclareAssert;
use StructuraPhp\Structura\Concerns\ExprScript\DependencyAssert as ScriptDependencyAssert;
use StructuraPhp\Structura\Contracts\ExceptInterface;
use StructuraPhp\Structura\Contracts\ExceptScriptInterface;
use StructuraPhp\Structura\Contracts\Expr\DependencyAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\MethodAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\NameAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\OtherAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\RelationAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\TypeAssertInterface;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Contracts\ExprIteratorAggregate;
use StructuraPhp\Structura\Contracts\ExprScript\DeclareAssertInterface as ScriptDeclareAssertInterface;
use StructuraPhp\Structura\Contracts\ExprScript\DependencyAssertInterface as ScriptDependencyAssertInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use Traversable;

/**
 * @implements ExprIteratorAggregate<class-string<ExprInterface>|ExprInterface>
 */
class Except implements ExprIteratorAggregate, TypeAssertInterface, DependencyAssertInterface, RelationAssertInterface, MethodAssertInterface, NameAssertInterface, OtherAssertInterface, ScriptDeclareAssertInterface, ScriptDependencyAssertInterface
{
    use TypeAssert;
    use DependencyAssert;
    use RelationAssert;
    use MethodAssert;
    use NameAssert;
    use OtherAssert;
    use ScriptDeclareAssert;
    use ScriptDependencyAssert;

    /** @var array<int, string> */
    private array $expects;

    /** @var array<int, class-string<ExprInterface>|ExprInterface> */
    private array $asserts = [];

    /**
     * @param array<int, string>|string $className file path or class name (with ::class) or array of both
     */
    public function __construct(array|string $className)
    {
        $this->expects = \is_array($className) ? $className : [$className];
    }

    public function getIterator(): Traversable
    {
        foreach ($this->asserts as $assert) {
            yield $assert;
        }
    }

    /**
     * @param class-string<ExprInterface> $expr
     */
    public function byAssert(
        string $expr,
    ): self {
        $this->asserts[] = $expr;

        return $this;
    }

    public function addExpr(ExprInterface $expr): static
    {
        $this->asserts[] = $expr instanceof ExceptInterface || $expr instanceof ExceptScriptInterface
            ? $expr
            : $expr::class;

        return $this;
    }

    public function isExcept(
        AbstractExpr|ExprInterface $expr,
        ScriptDescription $description,
    ): bool {
        if ($description instanceof ClassDescription) {
            $className = $description->isAnonymous()
                ? 'Anonymous'
                : $description->namespace;
        } else {
            $className = $description->namespace ?? $description->getFileBasename();
        }

        if (!in_array($className, $this->expects, true)) {
            return false;
        }

        if (\in_array($expr::class, $this->asserts, true)) {
            return true;
        }

        if (!$expr instanceof ExceptInterface && !$expr instanceof ExceptScriptInterface) {
            return false;
        }

        foreach ($this->asserts as $assert) {
            if ($assert instanceof $expr) {
                return $assert->except($expr, $description);
            }
        }

        return false;
    }
}

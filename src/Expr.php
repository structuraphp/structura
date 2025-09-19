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
use StructuraPhp\Structura\Contracts\Expr\DependencyAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\MethodAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\NameAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\OtherAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\RelationAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\TypeAssertInterface;
use StructuraPhp\Structura\Contracts\ExprScript\DeclareAssertInterface as ScriptDeclareAssertInterface;
use StructuraPhp\Structura\Contracts\ExprScript\DependencyAssertInterface as ScriptDependencyAssertInterface;

class Expr extends AbstractExpr implements TypeAssertInterface, DependencyAssertInterface, RelationAssertInterface, MethodAssertInterface, NameAssertInterface, OtherAssertInterface, ScriptDeclareAssertInterface, ScriptDependencyAssertInterface
{
    use TypeAssert;
    use DependencyAssert;
    use RelationAssert;
    use MethodAssert;
    use NameAssert;
    use OtherAssert;
    use ScriptDeclareAssert;
    use ScriptDependencyAssert;

    /** @var array<int,array<int,class-string>> */
    private array $attributDependencies = [];

    /** @var array<int,array<int,class-string>> */
    private array $extendDependencies = [];

    /** @var array<int,array<int,class-string>> */
    private array $implementDependencies = [];

    /** @var array<int,array<int,class-string>> */
    private array $traitDependencies = [];
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura;

use StructuraPhp\Structura\Concerns\Expr\ConstantAssert;
use StructuraPhp\Structura\Concerns\Expr\CorrespondingAssert;
use StructuraPhp\Structura\Concerns\Expr\DependencyAssert;
use StructuraPhp\Structura\Concerns\Expr\MethodAssert;
use StructuraPhp\Structura\Concerns\Expr\NameAssert;
use StructuraPhp\Structura\Concerns\Expr\RelationAssert;
use StructuraPhp\Structura\Concerns\Expr\ThridPartyAssert;
use StructuraPhp\Structura\Concerns\Expr\TypeAssert;
use StructuraPhp\Structura\Concerns\ExprScript\DependencyAssert as ScriptDependencyAssert;
use StructuraPhp\Structura\Concerns\ExprScript\ThirdPartyAssert as ScriptThirdPartyAssert;
use StructuraPhp\Structura\Contracts\Expr\ConstantAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\CorrespondingAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\DependencyAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\MethodAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\NameAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\RelationAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\ThirdPartyAssertInterface;
use StructuraPhp\Structura\Contracts\Expr\TypeAssertInterface;
use StructuraPhp\Structura\Contracts\ExprScript\DependencyAssertInterface as ScriptDependencyAssertInterface;
use StructuraPhp\Structura\Contracts\ExprScript\ThirdPartyAssertInterface as ScriptThirdPartyAssertInterface;

class Expr extends AbstractExpr implements TypeAssertInterface, DependencyAssertInterface, RelationAssertInterface, MethodAssertInterface, ConstantAssertInterface, NameAssertInterface, CorrespondingAssertInterface, ThirdPartyAssertInterface, ScriptThirdPartyAssertInterface, ScriptDependencyAssertInterface
{
    use TypeAssert;
    use DependencyAssert;
    use RelationAssert;
    use MethodAssert;
    use ConstantAssert;
    use NameAssert;
    use CorrespondingAssert;
    use ThridPartyAssert;
    use ScriptThirdPartyAssert;
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

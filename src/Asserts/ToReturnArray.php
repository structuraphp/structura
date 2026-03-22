<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToReturnArray implements ExprScriptInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to return array';
    }

    public function assert(ScriptDescription $description): bool
    {
        return $description->getRootReturn()?->expr instanceof Array_;
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ScriptDescription $description): array
    {
        $violation = $description->getRootReturn();

        if (!$violation instanceof Return_) {
            return [
                new ViolationValueObject(
                    \sprintf(
                        'Resource <promote>%s</promote> must return an array',
                        $description->getResourceName(),
                    ),
                    $this::class,
                    0,
                    $description->getFileBasename(),
                    $this->message,
                ),
            ];
        }

        return [
            new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must return an array but returns <fire>%s</fire>',
                    $description->getResourceName(),
                    $this->getExpressionType($violation->expr),
                ),
                $this::class,
                $violation->getLine(),
                $description->getFileBasename(),
                $this->message,
            ),
        ];
    }

    private function getExpressionType(?object $expr): string
    {
        if ($expr === null) {
            return 'void';
        }

        return match ($expr::class) {
            FuncCall::class => 'function call',
            MethodCall::class => 'method call',
            StaticCall::class => 'static call',
            Variable::class => 'variable',
            String_::class => 'string',
            Int_::class => 'integer',
            Float_::class => 'float',
            BooleanAnd::class,
            BooleanOr::class => 'boolean',
            Instanceof_::class => 'instanceof expression',
            New_::class => 'object instance',
            Ternary::class => 'ternary expression',
            Match_::class => 'match expression',
            default => 'unknown type',
        };
    }
}

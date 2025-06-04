<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use Attribute;
use InvalidArgumentException;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\ClassType;
use StructuraPhp\Structura\Enums\FlagType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToBeAttribute implements ExprInterface
{
    /**
     * @param int-mask-of<Attribute::IS_REPEATABLE|Attribute::TARGET_*> $flag
     */
    public function __construct(
        private int $flag,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'to be attribute';
    }

    public function assert(ClassDescription $class): bool
    {
        if (
            $class->classType !== ClassType::Class_
            || $class->attrGroups === []
            || $class->flags & FlagType::ModifierAbstract->value
        ) {
            return false;
        }

        foreach ($class->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() !== Attribute::class) {
                    continue;
                }

                try {
                    return $this->checkFlag($attr->args);
                } catch (InvalidArgumentException) {
                    return false;
                }
            }
        }

        return false;
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            \sprintf(
                'Resource <promote>%s</promote> must be attributable',
                $class->isAnonymous()
                    ? 'Anonymous'
                    : $class->namespace,
            ),
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }

    /**
     * @param array<int, Arg> $args
     */
    private function checkFlag(array $args): bool
    {
        if ($args === []) {
            return Attribute::TARGET_ALL === $this->flag;
        }

        $flagsToCheck = 0;
        foreach ($args as $arg) {
            $targets = $this->extractTargetValues($arg->value);

            foreach ($targets as $target) {
                $flagsToCheck |= $this->getTargetValue($target);
            }
        }

        return $flagsToCheck === $this->flag;
    }

    /**
     * @param array<int,Expr>|Expr $node
     *
     * @return array<int,string>
     */
    private function extractTargetValues(array|Expr $node): array
    {
        $targets = [];

        if ($node instanceof ClassConstFetch) {
            $name = $node->name;
            if (!$name instanceof Identifier) {
                throw new InvalidArgumentException();
            }

            $targets[] = $name->name;
        } elseif ($node instanceof BitwiseOr) {
            $targets = array_merge($targets, $this->extractTargetValues($node->left));
            $targets = array_merge($targets, $this->extractTargetValues($node->right));
        } elseif (is_array($node)) {
            foreach ($node as $child) {
                $targets = array_merge($targets, $this->extractTargetValues($child));
            }
        } else {
            throw new InvalidArgumentException('The node is not a valid flag');
        }

        return $targets;
    }

    private function getTargetValue(string $class): int
    {
        return match ($class) {
            'TARGET_CLASS' => Attribute::TARGET_CLASS,
            'TARGET_FUNCTION' => Attribute::TARGET_FUNCTION,
            'TARGET_METHOD' => Attribute::TARGET_METHOD,
            'TARGET_PROPERTY' => Attribute::TARGET_PROPERTY,
            'TARGET_CLASS_CONSTANT' => Attribute::TARGET_CLASS_CONSTANT,
            'TARGET_PARAMETER' => Attribute::TARGET_PARAMETER,
            'TARGET_ALL' => Attribute::TARGET_ALL,
            'IS_REPEATABLE' => Attribute::IS_REPEATABLE,
            default => 0,
        };
    }
}

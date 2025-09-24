<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Asserts;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingClass;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

#[CoversClass(ToHaveCorrespondingClass::class)]
#[CoversMethod(Expr::class, 'toHaveCorrespondingClass')]
class ToHaveCorrespondingClassTest extends TestCase
{
    use ArchitectureAsserts;

    private const CORRESPONDENCE_ERROR = [
        AndTest::class => 'StructuraPhp\Structura\Asserts\And',
        NotDependsOnFunctionTest::class => 'StructuraPhp\Structura\Asserts\NotDependsOnFunction',
        OrTest::class => 'StructuraPhp\Structura\Asserts\Or',
        ToBeInterfaceTest::class => 'StructuraPhp\Structura\Asserts\ToBeInterface',
        ToBeInvokableTest::class => 'StructuraPhp\Structura\Asserts\ToBeInvokable',
        ToUseStrictTypesTest::class => 'StructuraPhp\Structura\Asserts\ToUseStrictTypes',
    ];

    public function testToHaveCorrespondingClass(): void
    {
        $rules = $this
            ->allClasses()
            ->fromDir(dirname(__DIR__) . '/Visitors')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorrespondingClass(
                        static function (ClassDescription $classDescription): string {
                            $classname = preg_replace(
                                '/^(.+?)\\\Tests\\\Unit\\\(.+?)(Test)$/',
                                '$1\\\$2',
                                $classDescription->namespace ?? '',
                            );

                            return is_string($classname)
                                ? $classname
                                : throw new InvalidArgumentException('Classename must be a string');
                        },
                    ),
            );

        self::assertRulesPass(
            $rules,
            'to have corresponding class',
        );
    }

    public function testShouldFailToHaveCorrespondingClass(): void
    {
        $rules = $this
            ->allClasses()
            ->fromDir(__DIR__)
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toHaveCorrespondingClass(
                        static function (ClassDescription $classDescription): string {
                            $classname = preg_replace(
                                '/^(.+?)\\\Tests\\\Unit\\\(.+?)(Test)$/',
                                '$1\\\$2',
                                $classDescription->namespace ?? '',
                            );

                            return is_string($classname)
                                ? $classname
                                : throw new InvalidArgumentException('Classname not found');
                        },
                    ),
            );

        $output = [];
        foreach (self::CORRESPONDENCE_ERROR as $class => $except) {
            $output[] = sprintf(
                'Resource name <promote>%s</promote> must have corresponding class <promote>%s</promote>',
                $class,
                $except,
            );
        }

        self::assertRulesViolation(
            $rules,
            implode(', ', $output),
        );
    }
}

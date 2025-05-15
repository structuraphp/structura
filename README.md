# Structura

[![License](https://img.shields.io/github/license/soosyze/soosyze.svg)](https://github.com/soosyze/soosyze/blob/master/LICENSE "LICENSE")
[![PHP from Packagist](https://img.shields.io/badge/PHP-%3E%3D8.2-%238892bf)](/README.md#version-php "PHP version 8.2 minimum")

## About

Structura is an architectural testing tool for PHP, designed to help developers maintain a clean and
consistent code structure.

## Requirements

### PHP version

| Version PHP     | Structura 0.x |
|-----------------|---------------|
| <= 8.1          | ✗ Unsupported |
| 8.2 / 8.3 / 8.4 | ✓ Supported   |

## Installation

### Using Composer

```shell
composer required --dev structuraphp/structura
```

## Usage

## Configuration

Create the configuration file required for running the tests:

```shell
php structura init
```

At the root of your project, add the namespace and directory for your architecture tests:

```php
return static function (StructuraConfig $config): void {
    $config->archiRootNamespace(
        '<MY_NAMESPACE>\Tests\Architecture', // namespace
        'tests/Architecture', // test directory
    );
};
```

## Make test

After creating and completing your configuration file, you can use the command to create
architecture tests:

```shell
php bin/structura make
```

Here's a simple example of architecture testing for your DTOs:

```php
use StructuraPhp\Structura\Asserts\ToExtendNothing;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;
use Symfony\Component\Finder\Finder;

final class TestDto extends TestBuilder
{
    #[TestDox('Asserts rules Dto architecture')]
    public function testAssertArchitectureRules(): void
    {
        $this
            ->allClasses()
            ->fromDir(
                'app/Dto',
                fn(Finder $finder) => $finder->depth('== 0')
            )
            ->that($this->conditionThat(...))
            ->except($this->exception(...))
            ->should($this->should(...));
    }

    private function that(Expr $expr): void
    {
        // The rules will only apply to classes (ignore traits, enums, interfaces, etc.)
        $expr->toBeClasses();
    }

    private function exception(Except $except): void
    {
        // These classes will be ignored in the tests
        $except
            ->byClassname(
                className: [
                    FooDto::class,
                    BarDto::class,
                ],
                expression: ToExtendNothing::class
            );
    }

    private function should(Expr $expr): void
    {
        $expr
            ->toBeFinal()
            ->toBeReadonly()
            ->toHaveSuffix('Dto')
            ->toExtendsNothing()
            ->toHaveMethod('fromArray')
            ->toImplement(\JsonSerializable::class);
    }
}
```

### fromDir() and fromRaw()

Start with the `fromDir()` method, which takes the path of the files to be analysed.
It can take a second closure parameter
to [customise the finder](https://symfony.com/doc/current/components/finder.html):

```php
->fromDir(
    'src/Dto',
    static fn(Finder $finder): Finder => $finder->depth('== 0')
)
```

`fromRaw()` method can be used to test PHP code in the form of a string:

```php
->fromRaw('<?php
            
    use ArrayAccess;
    use Depend\Bap;
    use Depend\Bar;
            
    class Foo implements \Stringable {
        public function __construct(ArrayAccess $arrayAccess) {}

    public function __toString(): string {
        return $this->arrayAccess['foo'] ?? throw new \Exception();
    }
}')
```

### that()

Specifies rules for targeting class analysis, optional functionality:

```php
->that(static fn(Expr $expr): Expr => $expr->toBeClasses())
```

### except()

Ignores class rules, can be used as a baseline, optional functionality:

```php
->except(static fn(Except $except): Except => $except
    ->byClassname(
        className: [
            FooDto::class,
            BarDto::class,
        ],
        expression: ToExtendNothing::class
    )
)
```

### should()

List of architecture rules, required functionality:

```php
->should(static fn(Expr $expr): Expr => $expr
    ->toBeFinal()
    ->toBeReadonly()
    ->toHaveSuffix('Dto')
    ->toExtendsNothing()
    ->toHaveMethod('fromArray')
    ->toImplement(\JsonSerializable::class)
)
```

## First run

To run the architecture tests, execute the following command:

```shell
php bin/structura analyze
```

## Assertions

- 🧬 [Types](#types)
    - [toBeAbstract()](#tobeabstract)
    - [toBeAnonymousClasses()](#tobeanonymousclasses)
    - [toBeClasses()](#tobeclasses)
    - [toBeEnums()](#tobeenums)
    - [toBeFinal()](#tobefinal)
    - [toBeInterfaces()](#tobeinterfaces)
    - [toBeInvokable()](#tobeinvokable)
    - [toBeReadonly()](#tobereadonly)
    - [toBeTraits()](#tobetraits)
- 🔗 [Dependencies](#dependencies)
    - [dependsOnlyOn()](#dependsonlyon)
    - [toNotDependsOn()](#tonotdependson)
- 🧲 [Relation](#relation)
    - [toExtend()](#toextend)
    - [toExtendsNothing()](#toextendsnothing)
    - [toImplement()](#toimplement)
    - [toImplementNothing()](#toimplementnothing)
    - [toOnlyImplement()](#toonlyimplement)
    - [toUseTrait()](#tousetrait)
    - [toNotUseTrait()](#tonotusetrait)
    - [toOnlyUseTrait()](#toonlyusetrait)
- 🔌 [Method](#method)
    - [toHaveMethod()](#tohavemethod)
    - [toHaveConstructor()](#tohaveconstructor)
    - [toHaveDestructor()](#tohavedestructor)
- 🕶️ [Naming](#naming)
    - [toHavePrefix()](#tohaveprefix)
    - [toHaveSuffix()](#tohavesuffix)
- 🕹️ [Other](#other)
    - [toUseStrictTypes()](#tousestricttypes)
    - [toUseDeclare()](#tousedeclare)
    - [toHaveAttribute()](#tohaveattribute)
- 🗜️ [Operators](#operators)
    - [and()](#and)
    - [or()](#or)

## Types

### toBeAbstract()

```php
$this
  ->allClasses()
  ->fromRaw('<?php abstract class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeAbstract(),
  );
```

### toBeAnonymousClasses()

```php
$this
  ->allClasses()
  ->fromRaw('<?php new class {};')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeAnonymousClasses(),
  );
```

### toBeClasses()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeClasses(),
  );
```

### toBeEnums()

```php
$this
  ->allClasses()
  ->fromRaw('<?php enum Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeEnums(),
  );
```

### toBeFinal()

```php
$this
  ->allClasses()
  ->fromRaw('<?php final class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeFinal(),
  );
```

### toBeInterfaces()

```php
$this
  ->allClasses()
  ->fromRaw('<?php interface Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeInterfaces(),
  );
```

### toBeInvokable()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function __invoke() {} }')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeInvokable(),
  );
```

### toBeReadonly()

```php
$this
  ->allClasses()
  ->fromRaw('<?php readonly class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeReadonly(),
  );
```

### toBeTraits()

```php
$this
  ->allClasses()
  ->fromRaw('<?php trait Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeTraits(),
  );
```

## Dependencies

### dependsOnlyOn()

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->dependsOnlyOn(
        names: [ArrayAccess::class, /* ... */],
        patterns: ['App\Dto.+', /* ... */],
    )
  );
```

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select
dependencies

If you use the rule
classes ([toExtend()](#toextend), [toImplement()](#toimplement), [toOnlyImplement()](#toonlyimplement), [toHaveAttribute()](#tohaveattribute), [toOnlyUseTrait()](#toonlyusetrait), [toUseTrait()](#tousetrait)),
they are included by default in the permitted dependencies.

### toNotDependsOn()

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toNotDependsOn(
        names: [ArrayAccess::class, /* ... */],
        patterns: ['App\Dto.+', /* ... */],
    )
  );
```

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select
dependencies

## Relation

### toExtend()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo extends \Exception {}')
  ->should(fn(Expr $expr) => $expr->toExtend(Exception::class));
```

### toExtendsNothing()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toExtendsNothing());
```

### toImplement()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo implements \ArrayAccess, \JsonSerializable {}')
  ->should(fn(Expr $expr) => $expr->toImplement(ArrayAccess::class));
```

### toImplementNothing()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toImplementNothing());
```

### toOnlyImplement()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo implements \ArrayAccess {}')
  ->should(fn(Expr $expr) => $expr->toOnlyImplement(ArrayAccess::class));
```

### toUseTrait()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { use Bar, Baz; }')
  ->should(fn(Expr $expr) => $expr->toUseTrait(Bar::class));
```

### toNotUseTrait()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toNotUseTrait());
```

### toOnlyUseTrait()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { use Bar; }')
  ->should(fn(Expr $expr) => $expr->toOnlyUseTrait(Bar::class));
```

## Method

### toHaveMethod()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function bar() {} }')
  ->should(fn(Expr $expr) => $expr->toHaveMethod('bar'));
```

### toHaveConstructor()

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr->toHaveConstructor());
```

### toHaveDestructor()

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr->toHaveDestructor());
```

## Naming

### toHavePrefix()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class ExempleFoo {}')
  ->should(fn(Expr $expr) => $expr->toHavePrefix('Exemple'));
```

### toHaveSuffix()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class FooExemple {}')
  ->should(fn(Expr $expr) => $expr->toHaveSuffix('Exemple'));
```

## Other

### toUseStrictTypes()

```php
$this
  ->allClasses()
  ->fromRaw('<?php declare(strict_types=1); class Foo {}')
  ->should(fn(Expr $expr) => $expr->toUseStrictTypes());
```

### toUseDeclare()

```php
$this
  ->allClasses()
  ->fromRaw('<?php declare(encoding='ISO-8859-1'); class Foo {}')
  ->should(fn(Expr $expr) => $expr->toUseDeclare('encoding', 'ISO-8859-1'));
```

### toHaveAttribute()

```php
$this
  ->allClasses()
  ->fromRaw('<?php  #[\Deprecated] class Foo {}')
  ->should(fn(Expr $expr) => $expr->toHaveAttribute(Deprecated::class));
```

## Operators

## and()

## or()

## With PHPUnit


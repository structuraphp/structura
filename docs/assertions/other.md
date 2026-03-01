# 🕹️ Other Assertions

## toHaveAnonymousClass()

Assert that a class-like (class, enum, interface, trait) contains at least one anonymous class.
::: info Important

- With `allClasses()`: the anonymous class **MUST** be encapsulated inside the analyzed class-like.
- With `allScripts()`: detects any anonymous class present in the script, regardless of context.
  :::

### Example with allClasses()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function bar() { return new class {}; } }')
  ->should(fn(Expr $expr) => $expr->toHaveAnonymousClass());
```

### Example with allScripts()

```php
$this
  ->allScripts()
  ->fromRaw('<?php $obj = new class {};')
  ->should(fn(ExprScript $expr) => $expr->toHaveAnonymousClass());
```

## toNotHaveAnonymousClass()

Assert that a class-like does not contain any anonymous class.

### Example with allClasses()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function bar() {} }')
  ->should(fn(Expr $expr) => $expr->toNotHaveAnonymousClass());
```

### Example with allScripts()

```php
$this
  ->allScripts()
  ->fromRaw('<?php function foo() {}')
  ->should(fn(ExprScript $expr) => $expr->toNotHaveAnonymousClass());
```

## toHaveCorresponding()

Check the correspondence between a class/enum/interface/trait and a mask. To build the mask, you have access to the
description of the current class.
Correspondence rules can be used in many scenarios:

- If a model has a repository interface
- If a model has a policy with the same name
- If your controllers have associated queries or resources
  For example, you can check whether each unit test class has a corresponding class in your project:

```php
$this
    ->allClasses()
    ->fromDir('tests/Unit')
    ->should(
        static fn(Expr $assert): Expr => $assert
            ->toHaveCorrespondingClass(
                static fn (ClassDescription $classDescription): string => preg_replace(
                    '/^(.+?)\\\Tests\\\Unit\\\(.+?)(Test)$/',
                    '$1\\\$2',
                    $classDescription->namespace,
                )
            ),
    );
```

## toHaveCorrespondingClass()

Similar to [toHaveCorresponding()](#tohavecorresponding), but for matching with a class.

## toHaveCorrespondingEnum()

Similar to [toHaveCorresponding()](#tohavecorresponding), but for matching with an enum.

## toHaveCorrespondingInterface()

Similar to [toHaveCorresponding()](#tohavecorresponding), but for matching with an interface.

## toHaveCorrespondingTrait()

Similar to [toHaveCorresponding()](#tohavecorresponding), but for matching with a trait.

## toUseStrictTypes()

```php
$this
  ->allClasses()
  ->fromRaw('<?php declare(strict_types=1); class Foo {}')
  ->should(fn(Expr $expr) => $expr->toUseStrictTypes());
```

## toUseDeclare()

```php
$this
  ->allClasses()
  ->fromRaw("<?php declare(encoding='ISO-8859-1'); class Foo {}")
  ->should(fn(Expr $expr) => $expr->toUseDeclare('encoding', 'ISO-8859-1'));
```

## toBeInOneOfTheNamespaces()

Allows you to specifically target classes contained in a namespace.
::: info
Anonymous classes cannot have namespaces.
:::
You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select namespaces.

```php
$this
  ->allClasses()
  ->fromDir('tests')
  ->that(
    fn(Expr $expr) => $expr
      ->toBeInOneOfTheNamespaces('Tests\Unit.+')
  )
  ->should(fn(Expr $expr) => $expr /* our rules */);
```

## notToBeInOneOfTheNamespaces()

Allows you to specifically target classes **not** contained in a namespace.
::: info
Anonymous classes cannot have namespaces.
:::
You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select namespaces.

```php
$this
  ->allClasses()
  ->fromDir('tests')
  ->that(
    fn(Expr $expr) => $expr
      ->notToBeInOneOfTheNamespaces('Tests\Unit.+')
  )
  ->should(fn(Expr $expr) => $expr /* our rules */);
```

## toUseInclude()

Allows an inclusion (`include*`/`require*`) in a script or class.

```php
use StructuraPhp\Structura\Enums\IncludeType;
$this
    ->allScripts()
    ->fromRaw('<?php require "foo.php";')
    ->should(fn(ExprScript $expr) => $expr->toUseInclude(IncludeType::Require));
```

## toNotUseInclude()

```php
$this
    ->allScripts()
    ->fromRaw('<?php require "foo.php";')
    ->should(fn(ExprScript $expr) => $expr->toNotUseInclude());
```

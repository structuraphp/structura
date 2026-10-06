# 🔌 Method Assertions

## toHaveMethod()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function bar() {} }')
  ->should(fn(Expr $expr) => $expr->toHaveMethod('bar'));
```

## toNotHaveMethod()

Opposite of [toHaveMethod()](#tohavemethod). Fails if the resource declares the given method.

- Forbid magic accessors: `toNotHaveMethod('__get')`.
- Keep Value Objects free of setters: `toNotHaveMethod('setValue')`.

```php
$this
  ->allClasses()
  ->fromDir('src/Domain')
  ->should(fn(Expr $expr) => $expr->toNotHaveMethod('__get'));
```

## toHaveNoStaticMethod()

Fails for every static method declared by the resource, whatever its visibility. Static properties and
static closures are not methods and are ignored.

Example: Forbid static constructors (`make()`, `fromArray()`…) on classes resolved by the container.

```php
$this
  ->allClasses()
  ->fromDir('src/Domain/*/Action')
  ->should(fn(Expr $expr) => $expr->toHaveNoStaticMethod());
```

## toHaveOnlyPublicMethods()

Fails for every public method whose name is not in the given list; private and protected methods are
always allowed. Method names are compared case-insensitively, as in PHP.

Example: keep invokable classes focused: only `__construct()` and `__invoke()` are public.

```php
$this
  ->allClasses()
  ->fromDir('src/Domain/*/Action')
  ->should(fn(Expr $expr) => $expr->toHaveOnlyPublicMethods(['__construct', '__invoke']));
```

## toHaveConstructor()

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr->toHaveConstructor());
```

## toNotHaveConstructor()

Opposite of [toHaveConstructor()](#tohaveconstructor). Shortcut for
[toNotHaveMethod('__construct')](#tonothavemethod).

```php
$this
  ->allClasses()
  ->fromDir('src/Enum')
  ->should(fn(Expr $expr) => $expr->toNotHaveConstructor());
```

## toHaveDestructor()

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr->toHaveDestructor());
```

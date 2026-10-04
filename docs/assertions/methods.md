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

## toHaveConstructor()

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr->toHaveConstructor());
```

## toHaveDestructor()

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr->toHaveDestructor());
```

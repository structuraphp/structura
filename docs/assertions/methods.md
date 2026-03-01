# 🔌 Method Assertions

## toHaveMethod()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function bar() {} }')
  ->should(fn(Expr $expr) => $expr->toHaveMethod('bar'));
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

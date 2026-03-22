# 🔒 Constant Assertions

## toHaveConstant()

Assert that a class-like has at least one constant with the given visibility.

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public const BAR = 1; }')
  ->should(fn(Expr $expr) => $expr->toHaveConstant(VisibilityType::Public));
```

## toHavePublicConstant()

Shortcut for `toHaveConstant(VisibilityType::Public)`.

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public const BAR = 1; }')
  ->should(fn(Expr $expr) => $expr->toHavePublicConstant());
```

## toHaveProtectedConstant()

Shortcut for `toHaveConstant(VisibilityType::Protected)`.

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { protected const BAR = 1; }')
  ->should(fn(Expr $expr) => $expr->toHaveProtectedConstant());
```

## toHavePrivateConstant()

Shortcut for `toHaveConstant(VisibilityType::Private)`.

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { private const BAR = 1; }')
  ->should(fn(Expr $expr) => $expr->toHavePrivateConstant());
```

## toNotHaveConstant()

Assert that a class-like does not have any constant with the given visibility.

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toNotHaveConstant(VisibilityType::Public));
```

## toNotHavePublicConstant()

Shortcut for `toNotHaveConstant(VisibilityType::Public)`.

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { private const BAR = 1; }')
  ->should(fn(Expr $expr) => $expr->toNotHavePublicConstant());
```

## toNotHaveProtectedConstant()

Shortcut for `toNotHaveConstant(VisibilityType::Protected)`.

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public const BAR = 1; }')
  ->should(fn(Expr $expr) => $expr->toNotHaveProtectedConstant());
```

## toNotHavePrivateConstant()

Shortcut for `toNotHaveConstant(VisibilityType::Private)`.

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public const BAR = 1; }')
  ->should(fn(Expr $expr) => $expr->toNotHavePrivateConstant());
```

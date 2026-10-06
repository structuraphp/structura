# 🕶️ Naming Assertions

## toHavePrefix()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class ExempleFoo {}')
  ->should(fn(Expr $expr) => $expr->toHavePrefix('Exemple'));
```

## toNotHavePrefix()

Opposite of [toHavePrefix()](#tohaveprefix). Fails if the short class name starts with the given
prefix; anonymous classes always pass.

```php
$this
  ->allClasses()
  ->fromDir('src')
  ->should(fn(Expr $expr) => $expr->toNotHavePrefix('Abstract'));
```

## toHaveSuffix()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class FooExemple {}')
  ->should(fn(Expr $expr) => $expr->toHaveSuffix('Exemple'));
```

## toNotHaveSuffix()

Opposite of [toHaveSuffix()](#tohavesuffix). Fails if the short class name ends with the given
suffix; anonymous classes always pass.

```php
$this
  ->allClasses()
  ->fromDir('src/Contracts')
  ->should(fn(Expr $expr) => $expr->toNotHaveSuffix('Interface'));
```


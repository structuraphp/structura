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

::: warning
The check is a plain string comparison: `toNotHavePrefix('I')` also rejects `Invoice` or `Image`.
Prefer a distinctive prefix.
:::

```php
$this
  ->allClasses()
  ->fromDir('src')
  ->should(fn(Expr $expr) => $expr->toNotHavePrefix('Abstract'));
```

::: details Violation message
```
Resource name AbstractFoo must not start with Abstract
```
:::

## toHaveSuffix()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class FooExemple {}')
  ->should(fn(Expr $expr) => $expr->toHaveSuffix('Exemple'));
```


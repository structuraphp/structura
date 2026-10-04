# 🧲 Relation Assertions

## toExtend()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo extends \Exception {}')
  ->should(fn(Expr $expr) => $expr->toExtend(Exception::class));
```

## toNotExtend()

Opposite of [toExtend()](#toextend). Fails if the resource extends one of the given classes; one
violation is reported per forbidden parent. To forbid any parent, use
[toExtendNothing()](#toextendnothing).

```php
$this
  ->allClasses()
  ->fromDir('src/Domain')
  ->should(fn(Expr $expr) => $expr->toNotExtend(Model::class));
```

::: details Violation message
```
Resource Foo must not extend Model
```
:::

## toExtendNothing()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toExtendNothing());
```

## toImplement()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo implements \ArrayAccess, \JsonSerializable {}')
  ->should(fn(Expr $expr) => $expr->toImplement(ArrayAccess::class));
```

## toNotImplement()

Opposite of [toImplement()](#toimplement). Fails if the resource implements one of the given
interfaces; one violation is reported per forbidden interface. To forbid any interface, use
[toImplementNothing()](#toimplementnothing).

```php
$this
  ->allClasses()
  ->fromDir('src/Domain')
  ->should(fn(Expr $expr) => $expr->toNotImplement(Serializable::class));
```

::: details Violation message
```
Resource Foo must not implement Serializable
```
:::

## toImplementNothing()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toImplementNothing());
```

## toOnlyImplement()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo implements \ArrayAccess {}')
  ->should(fn(Expr $expr) => $expr->toOnlyImplement(ArrayAccess::class));
```

## toUseTrait()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { use Bar, Baz; }')
  ->should(fn(Expr $expr) => $expr->toUseTrait(Bar::class));
```

## toNotUseTrait()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toNotUseTrait());
```

## toOnlyUseTrait()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { use Bar; }')
  ->should(fn(Expr $expr) => $expr->toOnlyUseTrait(Bar::class));
```

## toHaveAttribute()

```php
$this
  ->allClasses()
  ->fromRaw('<?php #[\Deprecated] class Foo {}')
  ->should(fn(Expr $expr) => $expr->toHaveAttribute(Deprecated::class));
```

## toHaveNoAttribute()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toHaveNoAttribute());
```

## toHaveOnlyAttribute()

```php
$this
  ->allClasses()
  ->fromRaw('<?php #[\Deprecated] class Foo {}')
  ->should(fn(Expr $expr) => $expr->toHaveOnlyAttribute(Deprecated::class));
```


# 🧲 Relation Assertions

## toExtend()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo extends \Exception {}')
  ->should(fn(Expr $expr) => $expr->toExtend(Exception::class));
```

## toExtendsNothing()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toExtendsNothing());
```

## toImplement()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo implements \ArrayAccess, \JsonSerializable {}')
  ->should(fn(Expr $expr) => $expr->toImplement(ArrayAccess::class));
```

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


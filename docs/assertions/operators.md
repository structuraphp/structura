# 🗜️ Operators

## and()

To be valid, **all** the rules contained in the `and()` method must meet the requirements.

```php
// if Foo interface extends ArrayAccess and (JsonSerializable and Countable)
$this
  ->allClasses()
  ->fromRaw('<?php interface Foo extends ArrayAccess, JsonSerializable, Countable {}')
  ->should(fn(Expr $expr) => $expr
    ->toBeInterfaces()
    ->toExtend(ArrayAccess::class)
    ->and(fn(Expr $expr) => $expr
      ->toExtend(JsonSerializable::class)
      ->toExtend(Countable::class)
    )
  );
```

## or()

To be valid, **at least one** of the rules contained in the `or()` method must meet the requirements.

```php
// if Foo class implements ArrayAccess and (JsonSerializable or Countable)
$this
  ->allClasses()
  ->fromRaw('<?php class Foo implements ArrayAccess, JsonSerializable {}')
  ->should(fn(Expr $expr) => $expr
    ->toBeClasses()
    ->toImplement(ArrayAccess::class)
    ->or(fn(Expr $expr) => $expr
      ->toImplement(JsonSerializable::class)
      ->toImplement(Countable::class)
    )
  );
```

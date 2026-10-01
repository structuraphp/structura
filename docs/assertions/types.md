# 🧬 Type Assertions

## toBeAbstract()

```php
$this
  ->allClasses()
  ->fromRaw('<?php abstract class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeAbstract(),
  );
```

## toNotBeAbstract()

Opposite of [toBeAbstract()](#tobeabstract). Interfaces, traits, enums and anonymous classes always pass:
only an `abstract class` is a violation.

```php
$this
  ->allClasses()
  ->fromDir('src/Controller')
  ->should(
    static fn (Expr $assert): Expr => $assert->toNotBeAbstract(),
  );
```

## toBeAnonymousClasses()

A PHP script is considered an anonymous class **only** if it explicitly returns the anonymous class using a `return`
statement at the root level of the script.
::: warning
Simply instantiating an anonymous class (`new class {}`) is **not** sufficient.
The script **must** use `return new class {}` to be recognized as an anonymous class.
:::

```php
$this
  ->allClasses()
  ->fromRaw('<?php return new class {};')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeAnonymousClasses(),
  );
```

## toBeClasses()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeClasses(),
  );
```

## toBeEnums()

Must be a valid Unit Enum or Backed Enum.

```php
$this
  ->allClasses()
  ->fromRaw('<?php enum Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeEnums(),
  );
```

## toBeBackedEnums()

Must be a backed enumeration. If `ScalarType` is not specified, `int` and `string` are accepted.
See [PHP Backed Enumerations](https://www.php.net/manual/en/language.enumerations.backed.php).

```php
use StructuraPhp\Structura\Enums\ScalarType;
$this
  ->allClasses()
  ->fromRaw('<?php enum Foo: string {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeBackedEnums(ScalarType::String),
  );
```

## toBeFinal()

```php
$this
  ->allClasses()
  ->fromRaw('<?php final class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeFinal(),
  );
```

## toNotBeFinal()

Opposite of [toBeFinal()](#tobefinal). Interfaces, traits, enums and anonymous classes always pass:
only a `final class` is a violation. Useful for base classes meant to be extended.

```php
$this
  ->allClasses()
  ->fromDir('src/Base')
  ->should(
    static fn (Expr $assert): Expr => $assert->toNotBeFinal(),
  );
```

## toBeInterfaces()

```php
$this
  ->allClasses()
  ->fromRaw('<?php interface Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeInterfaces(),
  );
```

## toBeInvokable()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function __invoke() {} }')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeInvokable(),
  );
```

## toBeReadonly()

```php
$this
  ->allClasses()
  ->fromRaw('<?php readonly class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeReadonly(),
  );
```

## toBeTraits()

```php
$this
  ->allClasses()
  ->fromRaw('<?php trait Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeTraits(),
  );
```

## toBeAttributes()

Must be:

- A [syntax-compliant attribute](https://www.php.net/manual/en/language.attributes.classes.php)
- Instantiable by a [class reflection](https://www.php.net/manual/fr/language.attributes.reflection.php)
- Using [valid flags](https://www.php.net/manual/en/class.attribute.php#attribute.constants.target-class)

```php
$this
  ->allClasses()
  ->fromRaw('<?php #[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)] class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeAttributes(\Attribute::TARGET_CLASS_CONSTANT),
  );
```

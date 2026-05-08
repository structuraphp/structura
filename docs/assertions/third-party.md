# 🕹️ Other Assertions

## toHaveAnonymousClass()

Assert that a class-like (class, enum, interface, trait) contains at least one anonymous class.
::: info Important

- With `allClasses()`: the anonymous class **MUST** be encapsulated inside the analyzed class-like.
- With `allScripts()`: detects any anonymous class present in the script, regardless of context.
  :::

### Example with allClasses()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function bar() { return new class {}; } }')
  ->should(fn(Expr $expr) => $expr->toHaveAnonymousClass());
```

### Example with allScripts()

```php
$this
  ->allScripts()
  ->fromRaw('<?php $obj = new class {};')
  ->should(fn(ExprScript $expr) => $expr->toHaveAnonymousClass());
```

## toNotHaveAnonymousClass()

Assert that a class-like does not contain any anonymous class.

### Example with allClasses()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function bar() {} }')
  ->should(fn(Expr $expr) => $expr->toNotHaveAnonymousClass());
```

### Example with allScripts()

```php
$this
  ->allScripts()
  ->fromRaw('<?php function foo() {}')
  ->should(fn(ExprScript $expr) => $expr->toNotHaveAnonymousClass());
```

## toUseStrictTypes()

```php
$this
  ->allClasses()
  ->fromRaw('<?php declare(strict_types=1); class Foo {}')
  ->should(fn(Expr $expr) => $expr->toUseStrictTypes());
```

## toUseDeclare()

```php
$this
  ->allClasses()
  ->fromRaw("<?php declare(encoding='ISO-8859-1'); class Foo {}")
  ->should(fn(Expr $expr) => $expr->toUseDeclare('encoding', 'ISO-8859-1'));
```

## toBeInOneOfTheNamespaces()

Allows you to specifically target classes contained in a namespace.
::: info
Anonymous classes cannot have namespaces.
:::
You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select namespaces.

```php
$this
  ->allClasses()
  ->fromDir('tests')
  ->that(
    fn(Expr $expr) => $expr
      ->toBeInOneOfTheNamespaces('Tests\Unit.+')
  )
  ->should(fn(Expr $expr) => $expr /* our rules */);
```

## notToBeInOneOfTheNamespaces()

Allows you to specifically target classes **not** contained in a namespace.
::: info
Anonymous classes cannot have namespaces.
:::
You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select namespaces.

```php
$this
  ->allClasses()
  ->fromDir('tests')
  ->that(
    fn(Expr $expr) => $expr
      ->notToBeInOneOfTheNamespaces('Tests\Unit.+')
  )
  ->should(fn(Expr $expr) => $expr /* our rules */);
```

## toUseInclude()

Allows an inclusion (`include*`/`require*`) in a script or class.

```php
use StructuraPhp\Structura\Enums\IncludeType;
$this
    ->allScripts()
    ->fromRaw('<?php require "vendor/autoload.php";')
    ->should(fn(ExprScript $expr) => $expr->toUseInclude(IncludeType::Require));
```

### With a path pattern

An optional second parameter `$pathPattern` allows you to verify that the included path matches a glob pattern with [fnmatch](https://www.php.net/manual/en/function.fnmatch.php).

```php
use StructuraPhp\Structura\Enums\IncludeType;
$this
    ->allScripts()
    ->fromDir('src')
    ->should(fn(ExprScript $expr) => $expr->toUseInclude(IncludeType::Require, '*/vendor/autoload.php'));
```

The following expressions are statically resolved:

| Expression                           | Resolved value                      |
|--------------------------------------|-------------------------------------|
| `"literal/path.php"`                 | `literal/path.php`                  |
| `__DIR__ . "/file.php"`              | `{file_directory}/file.php`         |
| `__FILE__`                           | `{file_path}`                       |
| `dirname(__FILE__) . "/file.php"`    | `{file_directory}/file.php`         |
| `dirname(__FILE__, 2) . "/file.php"` | `{two_levels_up}/file.php`          |
| `dirname(__DIR__, N) . "/file.php"`  | `{N_levels_up}/file.php`            |
| `base_path() . "/file.php"` ¹        | `{registered_path}/file.php`        |

> ¹ Requires declaring the resolver with `addPathResolver()` in the configuration. See [configuration](/guide/configuration).

### With a custom path resolver

When a project uses helper functions to build paths (e.g. `base_path()`, `app_path()` in Laravel),
you can register them in the configuration so `toUseInclude` can resolve them statically.
The function arguments are **always ignored** — only the function name is matched and the registered path is returned directly.

```php
// structura.php
$config->addPathResolver('base_path', '/var/www');
$config->addPathResolver('app_path', '/var/www/app');
```

```php
use StructuraPhp\Structura\Enums\IncludeType;
$this
    ->allScripts()
    ->fromDir('bootstrap')
    ->should(fn(ExprScript $expr) => $expr->toUseInclude(IncludeType::Require, '*/vendor/autoload.php'));
```

The script `bootstrap/app.php` containing `require base_path() . "/vendor/autoload.php";`
will resolve to `/var/www/vendor/autoload.php` and match the pattern `*/vendor/autoload.php`.

::: warning
The function name `dirname` is reserved by the static analysis engine and **cannot** be registered as a path resolver.
:::

## toNotUseInclude()

```php
$this
    ->allScripts()
    ->fromRaw('<?php require "foo.php";')
    ->should(fn(ExprScript $expr) => $expr->toNotUseInclude());
```

## toHaveFilePermission()

Assert that a script or class file has specific Unix file permissions.

```php
$this
    ->allScripts()
    ->fromDir('src')
    ->should(fn(ExprScript $expr) => $expr->toHaveFilePermission('0644'));
```

## toReturnArray()

Assert that a PHP script returns an array at the root level using a `return` statement.

::: info
- The script **must** have a `return` statement at the root level (not nested inside functions, classes, matches or switch).
- The returned value **must** be an array literal using the `[]` syntax or `array()` construct (must not be in a variable or a function returning an array).
  :::

```php
$this
  ->allScripts()
  ->fromRaw('<?php return [];')
  ->should(fn(ExprScript $expr) => $expr->toReturnArray());
```

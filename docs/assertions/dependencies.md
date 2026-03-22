# 🔗 Dependency Assertions

## dependsOnlyOn()

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->dependsOnlyOn(
        names: [ArrayAccess::class, /* ... */],
        patterns: ['App\Dto.+', /* ... */],
    )
  );
```

## dependsOnlyOnAttribut()

If you use the rule [toHaveAttribute()](/assertions/relations#tohaveattribute), they are included by default in the
permitted dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->dependsOnlyOnAttribut(
        names: [\Attribute::class, /* ... */],
        patterns: ['Attributes\Custom.+', /* ... */],
    )
  );
```

## dependsOnlyOnImplementation()

If you use the rules [toImplement()](/assertions/relations#toimplement)
and [toOnlyImplement()](/assertions/relations#toonlyimplement), they are included by default in the permitted
dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->dependsOnlyOnImplementation(
        names: [\ArrayAccess::class, /* ... */],
        patterns: ['Contracts\Dto.+', /* ... */],
    )
  );
```

## dependsOnlyOnInheritance()

If you use the rule [toExtend()](/assertions/relations#toextend), they are included by default in the permitted
dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->dependsOnlyOnInheritance(
        names: [Controller::class, /* ... */],
        patterns: ['Controllers\Admin.+', /* ... */],
    )
  );
```

## dependsOnlyOnUseTrait()

If you use the rules [toUseTrait()](/assertions/relations#tousetrait)
and [toOnlyUseTrait()](/assertions/relations#toonlyusetrait), they are included by default in the permitted
dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->dependsOnlyOnUseTrait(
        names: [\HasFactor::class, /* ... */],
        patterns: ['Concerns\Models.+', /* ... */],
    )
  );
```

## toNotDependsOn()

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toNotDependsOn(
        names: [ArrayAccess::class, /* ... */],
        patterns: ['App\Dto.+', /* ... */],
    )
  );
```

## dependsOnlyOnFunction()

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->dependsOnlyOnFunction(
        names: ['strtolower', /* ... */],
        patterns: ['array_.+', /* ... */],
    )
  );
```

## toNotDependsOnFunction()

Prohibit the use of specific functions.
You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toNotDependsOnFunction(
        names: ['goto', /* ... */],
        patterns: ['.+exec', /* ... */],
    )
  );
```

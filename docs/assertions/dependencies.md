# 🔗 Dependency Assertions

## toOnlyDependOn()

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toOnlyDependOn(
        names: [ArrayAccess::class, /* ... */],
        patterns: ['App\Dto.+', /* ... */],
    )
  );
```

## toOnlyDependOnAttribute()

If you use the rule [toHaveAttribute()](/assertions/relations#tohaveattribute), they are included by default in the
permitted dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toOnlyDependOnAttribute(
        names: [\Attribute::class, /* ... */],
        patterns: ['Attributes\Custom.+', /* ... */],
    )
  );
```

## toOnlyDependOnImplementation()

If you use the rules [toImplement()](/assertions/relations#toimplement)
and [toOnlyImplement()](/assertions/relations#toonlyimplement), they are included by default in the permitted
dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toOnlyDependOnImplementation(
        names: [\ArrayAccess::class, /* ... */],
        patterns: ['Contracts\Dto.+', /* ... */],
    )
  );
```

## toOnlyDependOnInheritance()

If you use the rule [toExtend()](/assertions/relations#toextend), they are included by default in the permitted
dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toOnlyDependOnInheritance(
        names: [Controller::class, /* ... */],
        patterns: ['Controllers\Admin.+', /* ... */],
    )
  );
```

## toOnlyDependOnUseTrait()

If you use the rules [toUseTrait()](/assertions/relations#tousetrait)
and [toOnlyUseTrait()](/assertions/relations#toonlyusetrait), they are included by default in the permitted
dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toOnlyDependOnUseTrait(
        names: [\HasFactor::class, /* ... */],
        patterns: ['Concerns\Models.+', /* ... */],
    )
  );
```

## toNotDependOn()

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toNotDependOn(
        names: [ArrayAccess::class, /* ... */],
        patterns: ['App\Dto.+', /* ... */],
    )
  );
```

## toOnlyDependOnFunction()

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toOnlyDependOnFunction(
        names: ['strtolower', /* ... */],
        patterns: ['array_.+', /* ... */],
    )
  );
```

## toNotDependOnFunction()

Prohibit the use of specific functions.
You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toNotDependOnFunction(
        names: ['goto', /* ... */],
        patterns: ['.+exec', /* ... */],
    )
  );
```

## toOnlyDependOnPhpDoc()

Verifies that all class references appearing in phpDoc annotations (`@param`, `@return`, `@var`, `@throws`, etc.)
belong only to the authorised namespaces.

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toOnlyDependOnPhpDoc(
        names: [\ArrayAccess::class, /* ... */],
        patterns: ['App\Dto.+', /* ... */],
    )
  );
```

**Violation message:**
```
Resource <class> must only depend on these phpDoc namespaces <authorised> but depends on <forbidden>
```

## toNotDependOnPhpDoc()

Prohibit the use of specific class references inside phpDoc annotations.

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->toNotDependOnPhpDoc(
        names: [LegacyClass::class, /* ... */],
        patterns: ['Legacy\\.+', /* ... */],
    )
  );
```

**Violation message:**
```
Resource <class> must not depend on these phpDoc namespaces <forbidden> but depends on <found>
```



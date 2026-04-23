# 🔗 Corresponding Assertions

## toHaveCorresponding()

Check the correspondence between a class/enum/interface/trait and a mask. To build the mask, you have access to the
description of the current class.
Correspondence rules can be used in many scenarios:

- If a model has a repository interface
- If a model has a policy with the same name
- If your controllers have associated queries or resources

For example, you can check whether each unit test class has a corresponding class in your project:

```php
$this
    ->allClasses()
    ->fromDir('tests/Unit')
    ->should(
        static fn(Expr $assert): Expr => $assert
            ->toHaveCorrespondingClass(
                static fn (ClassDescription $classDescription): string => preg_replace(
                    '/^(.+?)\\\Tests\\\Unit\\\(.+?)(Test)$/',
                    '$1\\\$2',
                    $classDescription->namespace,
                )
            ),
    );
```

## toHaveCorrespondingClass()

Similar to [toHaveCorresponding()](#tohavecorresponding), but for matching with a class.

## toHaveCorrespondingEnum()

Similar to [toHaveCorresponding()](#tohavecorresponding), but for matching with an enum.

## toHaveCorrespondingFile()

Check the correspondence between a class and a file on disk. The assertion passes if `file_exists()` returns `true`.

This is useful to ensure that each class has a corresponding configuration file (e.g., migration, or any other file artifact).

```php
$this
    ->allClasses()
    ->fromDir('src/Enums')
    ->should(
        static fn(Expr $assert): Expr => $assert
            ->toHaveCorrespondingFile(
                static fn (ClassDescription $classDescription): string => sprintf(
                    '%s/lang/en/enums/%s.php',
                    dirname(__DIR__),
                    $classDescription->name,
                ),
            ),
    );
```

## toNotHaveCorrespondingFile()

Opposite of [toHaveCorrespondingFile()](#tohavecorrespondingfile). Check that no corresponding file exists on disk.

The assertion passes if `file_exists()` returns `false`.

```php
$this
    ->allClasses()
    ->fromDir('src/Enums')
    ->should(
        static fn(Expr $assert): Expr => $assert
            ->toNotHaveCorrespondingFile(
                static fn (ClassDescription $classDescription): string => sprintf(
                    '%s/lang/en/enums/%s.php',
                    dirname(__DIR__),
                    $classDescription->name,
                ),
            ),
    );
```

::: details Violation message
```
Resource name Foo must not have corresponding file /path/to/Foo.php
```
:::

## toHaveCorrespondingInterface()

Similar to [toHaveCorresponding()](#tohavecorresponding), but for matching with an interface.

## toHaveCorrespondingTrait()

Similar to [toHaveCorresponding()](#tohavecorresponding), but for matching with a trait.


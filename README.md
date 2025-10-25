# Structura

[![License](https://img.shields.io/github/license/soosyze/soosyze.svg)](https://github.com/soosyze/soosyze/blob/master/LICENSE "LICENSE")
[![PHP from Packagist](https://img.shields.io/badge/PHP-%3E%3D8.2-%238892bf)](/README.md#version-php "PHP version 8.2 minimum")

## About

Structura is an architectural testing tool for PHP, designed to help developers maintain a clean and
consistent code structure.

- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Make test](#make-test)
- [First run](#first-run)
- [Assertions](#assertions)
- [Custom assert](#custom-assert)
- [With PHPUnit](#with-phpunit)

## Requirements

### PHP version

| Version PHP     | Structura 0.x |
|-----------------|---------------|
| <= 8.1          | ✗ Unsupported |
| 8.2 / 8.3 / 8.4 | ✓ Supported   |

## Installation

### Using Composer

```shell
composer required --dev structuraphp/structura
```

## Usage

## Configuration

Create the configuration file required for running the tests:

```shell
php structura init
```

At the root of your project, add the namespace and directory for your architecture tests:

```php
return static function (StructuraConfig $config): void {
    $config->archiRootNamespace(
        '<MY_NAMESPACE>\Tests\Architecture', // namespace
        'tests/Architecture', // test directory
    );
};
```

## Make test

After creating and completing your configuration file, you can use the command to create
architecture tests:

```shell
php bin/structura make
```

Here's a simple example of architecture testing for your DTOs:

```php
use StructuraPhp\Structura\Asserts\ToExtendNothing;
use StructuraPhp\Structura\Attributes\TestDox;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Testing\TestBuilder;
use Symfony\Component\Finder\Finder;

final class TestDto extends TestBuilder
{
    #[TestDox('Asserts rules Dto architecture')]
    public function testAssertArchitectureRules(): void
    {
        $this
            ->allClasses()
            ->fromDir(
                'app/Dto',
                fn(Finder $finder) => $finder->depth('== 0')
            )
            ->that($this->conditionThat(...))
            ->except($this->exception(...))
            ->should($this->should(...));
    }

    private function that(Expr $expr): void
    {
        // The rules will only apply to classes (ignore traits, enums, interfaces, etc.)
        $expr->toBeClasses();
    }

    private function exception(Except $except): void
    {
        // These classes will be ignored in the tests
        $except
            ->byClassname(
                className: [
                    FooDto::class,
                    BarDto::class,
                ],
                expression: ToExtendNothing::class
            );
    }

    private function should(Expr $expr): void
    {
        $expr
            ->toBeFinal()
            ->toBeReadonly()
            ->toHaveSuffix('Dto')
            ->toExtendsNothing()
            ->toHaveMethod('fromArray')
            ->toImplement(\JsonSerializable::class);
    }
}
```

### toBeClasses() and allScripts()

There are two types of analysis:

```php
// Analysis of classes. A class MUST be present, otherwise an exception is raised.
$this->allClasses()

// Analysis of all PHP scripts.
$this->allScripts()
```

If you choose script analysis, all PHP code can be analysed, but only rules can be used.

### fromDir(), fromRaw() and fromRawMultiple()

Start with the `fromDir()` method, which takes the path of the files to be analysed.
It can take a second closure parameter
to [customise the finder](https://symfony.com/doc/current/components/finder.html):

```php
->fromDir(
    'src/Dto',
    static fn(Finder $finder): Finder => $finder->depth('== 0')
)
```

`fromRaw()` method can be used to test PHP code in the form of a string.
You can provide a file name as the second parameter. If it is null, it will be set by default with the following pattern : `tmp\run_<count>.php`

```php
->fromRaw(
    raw: '<?php
            
    use ArrayAccess;
    use Depend\Bap;
    use Depend\Bar;
            
    class Foo implements \Stringable {
        public function __construct(ArrayAccess $arrayAccess) {}
    
        public function __toString(): string {
            return $this->arrayAccess[\'foo\'] ?? throw new \Exception();
        }
    }',
    pathname: 'path/example_1.php'
)
```

`fromRawMultiple()` method can be used to test PHP code in the form of a string.
You can provide a file name as an array key. If it is numeric, it will be set by default with the following pattern: `tmp\run_<count>.php`

```php
->fromRawMultiple([
    'path/example_1.php' => '<?php /* ... */',
    'path/example_2.php' => '<?php /* ... */',
])
```

### that()

Specifies rules for targeting class analysis, optional functionality:

```php
// with allClasses()
->that(static fn(Expr $expr): Expr => $expr->toBeClasses())
// with allScript()
->that(static fn(ExprScript $expr): ExprScript => $expr->toBeClasses())
```

### except()

Ignores class rules, can be used as a baseline, optional functionality:

```php
->except(static fn(Except $except): Except => $except
    ->byClassname(
        className: [
            FooDto::class,
            BarDto::class,
        ],
        expression: ToExtendNothing::class
    )
)
```

### should()

List of architecture rules, required functionality:

```php
// with allClasses()
->should(static fn(Expr $expr): Expr => $expr
    ->toBeFinal()
    ->toBeReadonly()
    ->toHaveSuffix('Dto')
    ->toExtendsNothing()
    ->toHaveMethod('fromArray')
    ->toImplement(\JsonSerializable::class)
)
// with allScript()
->should(static fn(ExprScript $expr): ExprScript => $expr)
```

## First run

To run the architecture tests, execute the following command:

```shell
php bin/structura analyze
```

### Cli options

- `--format-error=<FORMAT>`
  - `text`: Default. For human consumption.
  - `github`: Creates GitHub Actions compatible output.

## Assertions

- 🧬 Types
  - [toBeAbstract()](#tobeabstract)
  - [toBeAnonymousClasses()](#tobeanonymousclasses)
  - [toBeClasses()](#tobeclasses)
  - [toBeEnums()](#tobeenums)
  - [toBeBackedEnums()](#tobebackedenums)
  - [toBeFinal()](#tobefinal)
  - [toBeInterfaces()](#tobeinterfaces)
  - [toBeInvokable()](#tobeinvokable)
  - [toBeReadonly()](#tobereadonly)
  - [toBeTraits()](#tobetraits)
  - [toBeAttribute()](#tobeattribute)
- 🔗 Dependencies
  - [dependsOnlyOn()](#dependsonlyon)
  - [dependsOnlyOnAttribut()](#dependsonlyonattribut)
  - [dependsOnlyOnImplementation()](#dependsonlyonimplementation)
  - [dependsOnlyOnInheritance()](#dependsonlyoninheritance)
  - [dependsOnlyOnUseTrait()](#dependsonlyonusetrait)
  - [toNotDependsOn()](#tonotdependson)
  - [dependsOnFunction()](#dependsonfunction)
  - [toNotDependsOnFunction()](#tonotdependsonfunction)
- 🧲 Relation
  - [toExtend()](#toextend)
  - [toExtendsNothing()](#toextendsnothing)
  - [toImplement()](#toimplement)
  - [toImplementNothing()](#toimplementnothing)
  - [toOnlyImplement()](#toonlyimplement)
  - [toUseTrait()](#tousetrait)
  - [toNotUseTrait()](#tonotusetrait)
  - [toOnlyUseTrait()](#toonlyusetrait)
  - [toHaveAttribute()](#tohaveattribute)
  - [toHaveNoAttribute()](#tohavenoattribute)
  - [toHaveOnlyAttribute()](#tohaveonlyattribute)
- 🔌 Method
  - [toHaveMethod()](#tohavemethod)
  - [toHaveConstructor()](#tohaveconstructor)
  - [toHaveDestructor()](#tohavedestructor)
- 🕶️ Naming
  - [toHavePrefix()](#tohaveprefix)
  - [toHaveSuffix()](#tohavesuffix)
- 🕹️ Other
  - [toHaveCorresponding()](#tohavecorresponding)
  - [toHaveCorrespondingClass()](#tohavecorrespondingclass)
  - [toHaveCorrespondingEnum()](#tohavecorrespondingenum)
  - [toHaveCorrespondingInterface()](#tohavecorrespondinginterface)
  - [toHaveCorrespondingTrait()](#tohavecorrespondingtrait)
  - [toUseStrictTypes()](#tousestricttypes)
  - [toUseDeclare()](#tousedeclare)
  - [toBeInOneOfTheNamespaces()](#tobeinoneofthenamespaces)
  - [notToBeInOneOfTheNamespaces()](#nottobeinoneofthenamespaces)
- 🗜️ Operators
  - [and()](#and)
  - [or()](#or)

### toBeAbstract()

```php
$this
  ->allClasses()
  ->fromRaw('<?php abstract class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeAbstract(),
  );
```

### toBeAnonymousClasses()

```php
$this
  ->allClasses()
  ->fromRaw('<?php new class {};')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeAnonymousClasses(),
  );
```

### toBeClasses()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeClasses(),
  );
```

### toBeEnums()

Must be a valid Unit Enum or Backed Enum.

```php
$this
  ->allClasses()
  ->fromRaw('<?php enum Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeEnums(),
  );
```

### toBeBackedEnums()

Must be a backed enumeration, if `ScalarType` is not specified, `int` and `string` are accepted.

https://www.php.net/manual/en/language.enumerations.backed.php

```php
use StructuraPhp\Structura\Enums\ScalarType;

$this
  ->allClasses()
  ->fromRaw('<?php enum Foo: string {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeBackedEnums(ScalarType::String),
  );
```

### toBeFinal()

```php
$this
  ->allClasses()
  ->fromRaw('<?php final class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeFinal(),
  );
```

### toBeInterfaces()

```php
$this
  ->allClasses()
  ->fromRaw('<?php interface Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeInterfaces(),
  );
```

### toBeInvokable()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function __invoke() {} }')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeInvokable(),
  );
```

### toBeReadonly()

```php
$this
  ->allClasses()
  ->fromRaw('<?php readonly class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeReadonly(),
  );
```

### toBeTraits()

```php
$this
  ->allClasses()
  ->fromRaw('<?php trait Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeTraits(),
  );
```

### toBeAttribute()

- Must be a [syntax-compliant attribute](https://www.php.net/manual/en/language.attributes.classes.php), 
- Must be instantiable by a [class reflection](https://www.php.net/manual/fr/language.attributes.reflection.php),
- And uses [valid flags](https://www.php.net/manual/en/class.attribute.php#attribute.constants.target-class).

```php
$this
  ->allClasses()
  ->fromRaw('<?php #[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)] class Foo {}')
  ->should(
    static fn (Expr $assert): Expr => $assert->toBeAttribute(\Attribute::TARGET_CLASS_CONSTANT),
  );
```

```php
<?php

[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)] // OK
class Foo {

}

#[Custom] // KO
class Bar {

}

(new ReflectionClass(Bar::class))->getAttributes()[0]->newInstance();
// Fatal error: Uncaught Error: Attribute class "Custom" not found
```

### dependsOnlyOn()

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

### dependsOnlyOnAttribut()

If you use the rule classes [toHaveAttribute()](#tohaveattribute), they are included by default in the permitted dependencies.

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

### dependsOnlyOnImplementation()

If you use the rule classes [toImplement()](#toimplement) and [toOnlyImplement()](#toonlyimplement) they are included by default in the permitted dependencies.

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

### dependsOnlyOnInheritance()

If you use the rule classes [toExtend()](#toextend) they are included by default in the permitted dependencies.

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

### dependsOnlyOnUseTrait()

If you use the rule classes [toUseTrait()](#tousetrait) and [toOnlyUseTrait()](#toonlyusetrait) they are included by default in the permitted dependencies.

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

### toNotDependsOn()

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

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies

### dependsOnFunction()

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

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies

### toNotDependsOnFunction()

Prohibit the use of function.

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr
    ->dependsOnlyOnFunction(
        names: ['goto', /* ... */],
        patterns: ['.+exec', /* ... */],
    )
  );
```

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select dependencies

### toExtend()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo extends \Exception {}')
  ->should(fn(Expr $expr) => $expr->toExtend(Exception::class));
```

### toExtendsNothing()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toExtendsNothing());
```

### toImplement()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo implements \ArrayAccess, \JsonSerializable {}')
  ->should(fn(Expr $expr) => $expr->toImplement(ArrayAccess::class));
```

### toImplementNothing()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toImplementNothing());
```

### toOnlyImplement()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo implements \ArrayAccess {}')
  ->should(fn(Expr $expr) => $expr->toOnlyImplement(ArrayAccess::class));
```

### toUseTrait()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { use Bar, Baz; }')
  ->should(fn(Expr $expr) => $expr->toUseTrait(Bar::class));
```

### toNotUseTrait()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toNotUseTrait());
```

### toOnlyUseTrait()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { use Bar; }')
  ->should(fn(Expr $expr) => $expr->toOnlyUseTrait(Bar::class));
```

### toHaveAttribute()

```php
$this
  ->allClasses()
  ->fromRaw('<?php #[\Deprecated] class Foo {}')
  ->should(fn(Expr $expr) => $expr->toHaveAttribute(Deprecated::class));
```

### toHaveNoAttribute()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr->toHaveNoAttribute());
```

### toHaveOnlyAttribute()

```php
$this
  ->allClasses()
  ->fromRaw('<?php #[\Deprecated] class Foo {}')
  ->should(fn(Expr $expr) => $expr->toHaveOnlyAttribute(Deprecated::class));
```

### toHaveMethod()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo { public function bar() {} }')
  ->should(fn(Expr $expr) => $expr->toHaveMethod('bar'));
```

### toHaveConstructor()

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr->toHaveConstructor());
```

### toHaveDestructor()

```php
$this
  ->allClasses()
  ->should(fn(Expr $expr) => $expr->toHaveDestructor());
```

### toHavePrefix()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class ExempleFoo {}')
  ->should(fn(Expr $expr) => $expr->toHavePrefix('Exemple'));
```

### toHaveSuffix()

```php
$this
  ->allClasses()
  ->fromRaw('<?php class FooExemple {}')
  ->should(fn(Expr $expr) => $expr->toHaveSuffix('Exemple'));
```

### toHaveCorresponding()

Check the correspondence between a class/enum/interface/trait and a mask.
To build the mask, you have access to the description of the current class.

Correspondence rules can be used in many scenarios, such as:
- If a model has a repository interface,
- If a model has a policy with the same name,
- If your controllers have associated queries or resources,
- ...

For example, you can check whether each unit test class has a corresponding class in your project :

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

### toHaveCorrespondingClass()

Similar to [toHaveCorresponding](#tohavecorresponding), but for matching with a class.

### toHaveCorrespondingEnum()

Similar to [toHaveCorresponding](#tohavecorresponding), but for matching with an enum.

### toHaveCorrespondingInterface()

Similar to [toHaveCorresponding](#tohavecorresponding), but for matching with an interface.

### toHaveCorrespondingTrait()

Similar to [toHaveCorresponding](#tohavecorresponding), but for matching with a trait.

### toUseStrictTypes()

```php
$this
  ->allClasses()
  ->fromRaw('<?php declare(strict_types=1); class Foo {}')
  ->should(fn(Expr $expr) => $expr->toUseStrictTypes());
```

### toUseDeclare()

```php
$this
  ->allClasses()
  ->fromRaw('<?php declare(encoding='ISO-8859-1'); class Foo {}')
  ->should(fn(Expr $expr) => $expr->toUseDeclare('encoding', 'ISO-8859-1'));
```

### toBeInOneOfTheNamespaces()

Allows you to specifically target classes contained in a namespace.

> Information !
> 
> Anonymous classes cannot have namespaces

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

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select namespaces.

### notToBeInOneOfTheNamespaces()

Allows you to specifically target classes not contained in a namespace.

> Information !
>
> Anonymous classes cannot have namespaces

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

You can use [regexes](https://www.php.net/manual/en/reference.pcre.pattern.syntax.php) to select namespaces.

## and()

To be valid, all the rules contained in the `and()` method must meet the requirements.

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

To be valid at least one of the rules contained in the `or()` method must meet the requirements.

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

## Custom assert

To create a custom rule :

- for class analysis, implement the `StructuraPhp\Structura\Contracts\ExprInterface` interface
- for script analysis, implement the `StructuraPhp\Structura\Contracts\ExprScriptInterface` interface.

```php
<?php

final readonly class CustomRule implements ExprInterface
{
    public function __construct(
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return 'exemple'; // Name of the rule
    }

    public function assert(ClassDescription $class): bool
    {
        return true; // Must return false if the test fails
    }

    public function getViolation(ClassDescription $class): ViolationValueObject
    {
        return new ViolationValueObject(
            'error message', // Console output
            $this::class,
            $class->lines,
            $class->getFileBasename(),
            $this->message,
        );
    }
}
```

To use a custom rule, add it using the `addExpr()` method.

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr
    ->addExpr(new CustomRule('foo'))
  );
```

Use [existing rules](https://github.com/structuraphp/structura/tree/main/src/Asserts) as an example.

## With PHPUnit

Structura can integrate architecture testing with PHPUnit with this project:

<https://github.com/structuraphp/structura-phpunit>

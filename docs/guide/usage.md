# Usage

## Make Test

After creating and completing your configuration file, you can use the command to create architecture tests:

```shell
php bin/structura make:test
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
        $expr->toBeClasses();
    }

    private function exception(Except $except): void
    {
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

## Analysis Types

### `allClasses()`

Analysis of classes. A class **MUST** be present, otherwise an exception is raised.

```php
$this->allClasses()
```

### `allScripts()`

Analysis of all PHP scripts. All PHP code can be analysed, but only script rules can be used.

```php
$this->allScripts()
```

## File Selection

### `fromDir()`

Takes the path of the files to be analysed. It accepts an optional second closure parameter to [customise the Finder](https://symfony.com/doc/current/components/finder.html):

```php
->fromDir(
    'src/Dto',
    static fn(Finder $finder): Finder => $finder->depth('== 0')
)
```

### `fromRaw()`

Test PHP code in the form of a string. You can provide a file name as the second parameter. If `null`, it defaults to `tmp\run_<count>.php`:

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

### `fromRawMultiple()`

Test multiple PHP code snippets. You can provide file names as array keys. If numeric, they default to `tmp\run_<count>.php`:

```php
->fromRawMultiple([
    'path/example_1.php' => '<?php /* ... */',
    'path/example_2.php' => '<?php /* ... */',
])
```

## Filtering

### `that()`

Specifies rules for targeting class analysis (optional):

```php
// with allClasses()
->that(static fn(Expr $expr): Expr => $expr->toBeClasses())

// with allScripts()
->that(static fn(ExprScript $expr): ExprScript => $expr->toBeClasses())
```

## Exceptions

### `except()`

Exclude specific classes or patterns from rules.

#### `byClassname()`

Ignore rules for specific classes by their fully qualified class name:

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

#### `byNamespace()`

Ignore rules for classes in specific namespaces (supports regex patterns):

```php
->except(static fn(Except $except): Except => $except
    ->byNamespace(
        namespace: 'App\Tests\.*',
        expression: ToExtendNothing::class
    )
)
```

#### `byFileName()`

Ignore rules for scripts matching a filename regex pattern:

```php
->except(static fn(Except $except): Except => $except
    ->byFileName(
        filePattern: '/migrations\/.*\.php$/',
        expression: ToBeClasses::class
    )
)
```

## Rules

### `should()`

List of architecture rules (required):

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

// with allScripts()
->should(static fn(ExprScript $expr): ExprScript => $expr)
```


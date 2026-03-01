# Custom Assert

To create a custom rule:

- For **class analysis**, implement the `StructuraPhp\Structura\Contracts\ExprInterface` interface.
- For **script analysis**, implement the `StructuraPhp\Structura\Contracts\ExprScriptInterface` interface.

## Creating a Custom Rule

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

## Using a Custom Rule

To use a custom rule, add it using the `addExpr()` method:

```php
$this
  ->allClasses()
  ->fromRaw('<?php class Foo {}')
  ->should(fn(Expr $expr) => $expr
    ->addExpr(new CustomRule('foo'))
  );
```

::: tip
Use [existing rules](https://github.com/structuraphp/structura/tree/main/src/Asserts) as examples for implementing your own.
:::


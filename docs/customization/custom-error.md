# Custom Error Format

To create a custom error format, implement the `StructuraPhp\Structura\Contracts\ErrorFormatterInterface` interface.

## Creating a Custom Error Format

```php
class CustomError implements ErrorFormatterInterface
{
    public function formatErrors(
        AnalyseValueObject $analyseValueObject,
        OutputInterface $output,
    ): int {
        // TODO: Implement formatErrors() method.
    }
}
```

## Registering

Add your implementation to the configuration:

```php
$config->setErrorFormatter(
    'custom',
    new CustomError()
);
```

Then run the analysis with the `--error-format` option:

```shell
php bin/structura analyze --error-format=custom
```


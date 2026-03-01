# Configuration

Create the configuration file required for running the tests:

```shell
php structura init
```

At the root of your project, add the namespace and directory for your architecture tests:

```php
return static function (StructuraConfig $config): void {
    // Test suite, required to start the analysis
    $config->addTestSuite('tests/Architecture', 'main');
    // Base namespace, required for the test creation command
    $config->archiRootNamespace(
        '<MY_NAMESPACE>\Tests\Architecture', // namespace
        'tests/Architecture', // test directory
    );
};
```

## Autoload

If you use custom formats, progress bars, or rules with the PHAR file, add your autoloader to the configuration:

```php
$config->setAutoload(__DIR__ . '/vendor/autoload.php');
```


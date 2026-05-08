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

## Path resolvers

Some projects use helper functions to build include/require paths (e.g. `base_path()` or `app_path()` in Laravel).
By default, Structura cannot resolve these calls statically and treats them as **dynamic paths**, which triggers a violation when a path pattern is set on `toUseInclude()`.

Register a path resolver to tell Structura what a function call resolves to:

```php
$config->addPathResolver('base_path', '/var/www');
$config->addPathResolver('app_path', '/var/www/app');
```

| Parameter       | Type     | Description                                                          |
|-----------------|----------|----------------------------------------------------------------------|
| `$functionName` | `string` | The function name to match in the AST (e.g. `"base_path"`)          |
| `$path`         | `string` | The absolute or relative path the function represents (e.g. `"/var/www"`) |

- The function **arguments are ignored** — only the name is matched.
  Use concatenation to append dynamic segments: `base_path() . "/vendor/autoload.php"`.
- The function name `dirname` is **reserved** by the static analysis engine.

**Example — Laravel project:**

```php
// structura.php
return static function (StructuraConfig $config): void {
    $config->addTestSuite('tests/Architecture', 'main');
    $config->addPathResolver('base_path', __DIR__);
    $config->addPathResolver('app_path', __DIR__ . '/app');
};
```

```php
// tests/Architecture/BootstrapTest.php
use StructuraPhp\Structura\Enums\IncludeType;

$this
    ->allScripts()
    ->fromDir('bootstrap')
    ->should(fn(ExprScript $expr) => $expr->toUseInclude(IncludeType::Require, '*/vendor/autoload.php'));
```


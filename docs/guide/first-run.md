# First Run & CLI Options

## Running the Analysis

To run the architecture tests, execute the following command:

```shell
php vendor/bin/structura analyze
```

## CLI Options

| Option                                    | Description                                                                         |
|-------------------------------------------|-------------------------------------------------------------------------------------|
| `-c, --config[=CONFIG]`                   | Path to config file.                                                                |
| `--filter[=FILTER]`                       | Filter which tests to run by text in the test name (class or method). See below.    |
| `--testsuite[=TESTSUITE]`                 | List available test suites as defined in the PHP configuration file.                |
| `-f, --error-format[=ERROR-FORMAT]`       | Error output format (see below).                                                    |
| `--no-error`                              | Disable error output.                                                               |
| `-p, --progress-format[=PROGRESS-FORMAT]` | Progress output format (see below).                                                 |
| `--no-progress`                           | Disable progress output.                                                            |
| `--stop-on-error`                         | Stop execution upon first error.                                                    |
| `--stop-on-warning`                       | Stop execution after the first warning, and fail.                                   |
| `--stop-on-notice`                        | Stop execution after the first notice, and fail.                                    |
| `--generate-baseline`                     | Record the current violations in the baseline file (see [Baseline](./baseline.md)). |

### Filter

`--filter` keeps the tests whose class or method name contains the given text, whatever the case. A class that matches
keeps all its methods.

Separate several values with `|`: a test is kept when its name contains one of them. The values are plain text, not
regular expressions.

```sh
php vendor/bin/structura analyze --filter=Controller
php vendor/bin/structura analyze --filter='TestController|TestAssert'
```

::: warning
Quote the value when it contains several values: `|` is a special character for the shell.
:::

### Error Formats

| Format        | Description                                                                                                  |
|---------------|--------------------------------------------------------------------------------------------------------------|
| `text`        | Default. For human consumption.                                                                              |
| `github`      | Creates GitHub Actions compatible output.                                                                    |
| `gitlab`      | Creates Gitlab Actions compatible output.                                                                    |
| `pretty-json` | Human-readable JSON with indentation. Includes styled formatter tags in error messages for further analysis. |
| `json`        | Minified JSON output for machine consumption. Same structure as `pretty-json` without indentation.           |

::: tip
You can disable error output entirely with the `--no-error` option. Note that even when error output is disabled, the
command will still return a non-zero exit code if violations are found.
:::

### Progress Formats

| Format | Description                     |
|--------|---------------------------------|
| `text` | Default. For human consumption. |
| `bar`  | Progress bar display.           |

::: tip
You can disable progress output entirely with the `--no-progress` option.
:::



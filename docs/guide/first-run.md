# First Run & CLI Options

## Running the Analysis

To run the architecture tests, execute the following command:

```shell
php vendor/bin/structura analyze
```

## CLI Options

| Option                                    | Description                                                                          |
|-------------------------------------------|--------------------------------------------------------------------------------------|
| `-c, --config[=CONFIG]`                   | Path to config file.                                                                 |
| `--filter[=FILTER]`                       | Filter which tests to run using pattern matching on the test name (class or method). |
| `--testsuite[=TESTSUITE]`                 | List available test suites as defined in the PHP configuration file.                 |
| `-f, --error-format[=ERROR-FORMAT]`       | Error output format (see below).                                                     |
| `--no-error`                              | Disable error output.                                                                |
| `-p, --progress-format[=PROGRESS-FORMAT]` | Progress output format (see below).                                                  |
| `--no-progress`                           | Disable progress output.                                                             |
| `--stop-on-error`                         | Stop execution upon first error.                                                     |
| `--stop-on-warning`                       | Stop execution after the first warning.                                              |
| `--stop-on-notice`                        | Stop execution after the first notice.                                               |

### Error Formats

| Format        | Description                                                                                                  |
|---------------|--------------------------------------------------------------------------------------------------------------|
| `text`        | Default. For human consumption.                                                                              |
| `github`      | Creates GitHub Actions compatible output.                                                                    |
| `gitlab`      | Creates Gitlab Actions compatible output.                                                                    |
| `pretty-json` | Human-readable JSON with indentation. Includes styled formatter tags in error messages for further analysis. |
| `json`        | Minified JSON output for machine consumption. Same structure as `pretty-json` without indentation.           |

::: tip
You can disable error output entirely with the `--no-error` option. Note that even when error output is disabled, the command will still return a non-zero exit code if violations are found.
:::

### Progress Formats

| Format  | Description                     |
|---------|---------------------------------|
| `text`  | Default. For human consumption. |
| `bar`   | Progress bar display.           |

::: tip
You can disable progress output entirely with the `--no-progress` option.
:::



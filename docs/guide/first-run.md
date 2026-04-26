# First Run & CLI Options

## Running the Analysis

To run the architecture tests, execute the following command:

```shell
php bin/structura analyze
```

## CLI Options

| Option | Description |
|--------|-------------|
| `-c, --config[=CONFIG]` | Path to config file. |
| `--filter[=FILTER]` | Filter which tests to run using pattern matching on the test name (class or method). |
| `--testsuite[=TESTSUITE]` | List available test suites as defined in the PHP configuration file. |
| `-f, --error-format[=ERROR-FORMAT]` | Error output format (see below). |
| `-p, --progress-format[=PROGRESS-FORMAT]` | Progress output format (see below). |
| `--stop-on-error` | Stop execution upon first error. |
| `--stop-on-warning` | Stop execution after the first warning. |
| `--stop-on-notice` | Stop execution after the first notice. |
| `--no-progress` | Disable progress output. |

### Error Formats

| Format | Description |
|--------|-------------|
| `text` | Default. For human consumption. |
| `github` | Creates GitHub Actions compatible output. |

### Progress Formats

| Format | Description |
|--------|-------------|
| `text` | Default. For human consumption. |
| `bar` | Progress bar display. |


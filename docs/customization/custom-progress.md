# Custom Progress Bar

To create a custom progress bar, implement the `StructuraPhp\Structura\Contracts\ProgressFormatterInterface` interface.

## Creating a Custom Progress Bar

```php
class CustomProgress implements ProgressFormatterInterface
{
    private int $max;
    private int $counter = 0;

    public function progressStart(OutputInterface $output, int $max): void
    {
        $this->max = $max;
        $output->writeln('<info>Starting progress ...</info>');
    }

    public function progressAdvance(OutputInterface $output, AnalyseValueObject $analyseValueObject): void
    {
        $this->counter++;
        $output->writeln(sprintf('%d/%d', $this->counter, $this->max));
    }

    public function progressFinish(OutputInterface $output): void
    {
        $output->writeln('<info>Finished progress ...</info>');
    }
}
```

## Registering

Add your implementation to the configuration:

```php
$config->setProgressFormatter(
    'custom',
    new CustomProgress()
);
```

Then run the analysis with the `--progress-format` option:

```shell
php bin/structura analyze --progress-format=custom
```

## Output Example

```
Starting progress ...
1/4
2/4
3/4
4/4
Finished progress ...
```


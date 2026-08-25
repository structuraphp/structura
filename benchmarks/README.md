# Benchmarks

Performance benchmarks based on [PHPBench](https://phpbench.readthedocs.io), used to
check whether a change improves or degrades the runtime of the assertions, the parser
and its visitors, the analysis orchestration and the console formatters.

## Layout

| Path                             | Role                                                                        |
|----------------------------------|-----------------------------------------------------------------------------|
| `Fixture/`                       | Frozen corpus of 44 PHP files analysed by every benchmark                   |
| `Suite/`                         | Frozen `TestBuilder` classes (pass / violation + warning / notice)          |
| `Corpus.php`                     | Finder and parsed descriptions shared by the benchmarks                     |
| `AnalyseValueObjectFactory.php`  | Frozen analysis result, input of `FormatterBench`                           |
| `AssertBench.php`                | One variant per assertion of `src/Asserts` (`assert()` and `getViolation()`)|
| `VisitorBench.php`               | Description building, per visitor and as the full `ParseService` pipeline   |
| `ParseServiceBench.php`          | Cost of the finder, the parser and the visitors                             |
| `ExecuteServiceBench.php`        | Whole architecture test: finder, parser, `that`/`except`, assertions, events|
| `AnalyseOrchestratorBench.php`   | Orchestration: discovery, reflection, events, merge, `--filter`, stop-on    |
| `FormatterBench.php`             | The 6 error formatters and the 3 progress formatters                        |

Each benchmark keeps the setup out of the measurement: `AssertBench` parses the corpus
once per iteration in its before method, `VisitorBench` pre-parses it into raw ASTs, and
`FormatterBench` builds its analysis result up front.

## Compare a change

```bash
# on the reference code (develop, or before your change)
make bench-baseline

# after your change
make bench
```

`make bench` fails when a variant is more than 10% slower than the baseline, and names
the faulty variant. Narrow a run down with `args`:

```bash
make bench args="--filter=benchAssert --variant=toBeFinal"
make bench-baseline args="--filter=ExecuteServiceBench"
make bench-report
```

Results are stored in `build/phpbench/storage` (git ignored).

## Hybrid CPUs (Intel P/E cores)

On a CPU mixing performance and efficiency cores, the same code is measured up to twice
as slow depending on the core the process lands on — enough to produce phantom
regressions of +90%. Pin the run to one performance core:

```bash
make bench-baseline BENCH_CPU=2
make bench BENCH_CPU=2
```

Find a performance core (highest `cpuinfo_max_freq`):

```bash
for c in /sys/devices/system/cpu/cpu[0-9]*; do
    echo "$(basename "$c") $(cat "$c/cpufreq/cpuinfo_max_freq" 2>/dev/null)"
done | sort -k2 -rn | head
```

Use the same `BENCH_CPU` for the baseline and for the comparison. On a homogeneous CPU
(most CI runners), leave it unset.

The regression threshold is `baseline * 1.10 + 1 microsecond`: the absolute part keeps
sub-microsecond variants (`errorNone`, trivial assertions) from failing on noise.

## Rules

- **Never modify `Fixture/`**: any change to the corpus invalidates every stored
  baseline and turns the comparison into noise. It is excluded from PHP-CS-Fixer,
  PHPStan and Rector for that reason.
- **Add a variant when adding an assertion or a formatter**: a new entry in
  `AssertBench::asserts()` or `FormatterBench::formatters()` is enough, the param
  providers and the subjects pick it up automatically.
- **`Suite/` and `AnalyseValueObjectFactory` are frozen too**: changing a rule or the
  shape of the analysis result changes what the orchestrator and the formatters measure.
- **Stay PHP 8.2 compatible**: the corpus is parsed with the host parser, and the project
  supports PHP 8.2 — no typed class constants or newer syntax anywhere under `benchmarks/`.
- **Benchmark on an idle machine**: the tolerance is 10%, background load produces
  false regressions.

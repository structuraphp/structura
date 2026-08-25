<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks;

use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToBeClasses;
use StructuraPhp\Structura\Asserts\ToBeFinal;
use StructuraPhp\Structura\Benchmarks\Fixture\Models\Order;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use StructuraPhp\Structura\ValueObjects\RuleValuesObject;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

/**
 * Frozen analysis result used as the input of FormatterBench.
 *
 * Built by hand rather than derived from a real run: the formatters must be
 * measured on a constant payload, otherwise a change in the assertions or in
 * the parser would move their timings without them having changed.
 *
 * The rules use raw sources only, never a Finder: ProgressTextFormatter calls
 * Finder::count(), which would make the benchmark measure the filesystem.
 *
 * Do not change the shape (number of tests, violations, warnings, notices),
 * it would invalidate the stored baselines.
 */
final readonly class AnalyseValueObjectFactory
{
    private const VIOLATIONS_PER_ASSERT = 6;

    public static function create(): AnalyseValueObject
    {
        $analyseTestValueObjects = [
            self::test('TestBenchType', 'Type architecture rules', [
                'to be final' => 0,
                'to be readonly' => 0,
                'to be classes' => 1,
                'to be interfaces' => 1,
                'to be enums' => 2,
                'to use declare' => 1,
            ]),
            self::test('TestBenchDependency', 'Dependency architecture rules', [
                'to not depends on these namespaces <promote>Symfony</promote>' => 0,
                'depends only on these namespaces <promote>App</promote>' => 0,
                'to not depends on function' => 1,
                'to have no attribute' => 3,
            ]),
            self::test('TestBenchNaming', 'Naming architecture rules', [
                'to have prefix <promote>To</promote>' => 0,
                'to have suffix <promote>Dto</promote>' => 0,
                'to have method <promote>__invoke</promote>' => 0,
                'to extend nothing' => 1,
            ]),
        ];

        $countPass = 0;
        $countViolation = 0;
        $countWarning = 0;
        $countNotice = 0;

        foreach ($analyseTestValueObjects as $analyseTestValueObject) {
            $countPass += $analyseTestValueObject->assertValueObject->countAssertsSuccess();
            $countViolation += $analyseTestValueObject->assertValueObject->countAssertsFailure();
            $countWarning += $analyseTestValueObject->assertValueObject->countAssertsWarning();
            $countNotice += $analyseTestValueObject->assertValueObject->countAssertsNotices();
        }

        return new AnalyseValueObject(
            timeStart: microtime(true),
            countPass: $countPass,
            countViolation: $countViolation,
            countWarning: $countWarning,
            countNotice: $countNotice,
            analyseTestValueObjects: $analyseTestValueObjects,
        );
    }

    /**
     * @param array<string, int> $pass
     */
    private static function test(string $classname, string $testDox, array $pass): AnalyseTestValueObject
    {
        $violations = [];
        $warnings = [];
        $notices = [];

        foreach ($pass as $message => $state) {
            if ($state === 0) {
                $violations[$message] = self::violations($message);

                continue;
            }

            if ($state === 2) {
                $warnings[$message] = [
                    sprintf(
                        '<promote>%s</promote> exception for <promote>%s</promote> is no longer applicable',
                        ToBeFinal::class,
                        Order::class,
                    ),
                ];

                continue;
            }

            if ($state === 3) {
                $notices[$message] = sprintf(
                    'No PHP files found for test "<promote>%s</promote>". Assertions were skipped.',
                    $classname,
                );
            }
        }

        return new AnalyseTestValueObject(
            source: new SourceTestValueObject(
                testClassname: 'StructuraPhp\Structura\Benchmarks\Suite\\' . $classname,
                textDox: $testDox,
                methodName: 'testArchitecture',
                line: 21,
                pathname: 'benchmarks/Suite/' . $classname . '.php',
            ),
            ruleValueObjects: [
                new RuleValuesObject(
                    raws: [
                        'benchmarks/Fixture/Models/Order.php' => '<?php final class Order {}',
                        'benchmarks/Fixture/Models/Model.php' => '<?php abstract class Model {}',
                    ],
                    finder: null,
                    that: (new AbstractExpr())->addExpr(new ToBeClasses()),
                    except: null,
                    should: (new AbstractExpr())->addExpr(new ToBeFinal()),
                ),
            ],
            assertValueObject: new AssertValueObject(
                pass: $pass,
                violations: $violations,
                warnings: $warnings,
                notices: $notices,
            ),
        );
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    private static function violations(string $message): array
    {
        $violations = [];

        for ($i = 0; $i < self::VIOLATIONS_PER_ASSERT; $i++) {
            $violations[] = new ViolationValueObject(
                messageViolation: sprintf(
                    'Resource <promote>StructuraPhp\Structura\Benchmarks\Fixture\Models\Model%d</promote> must %s',
                    $i,
                    $message,
                ),
                assertClassname: ToBeFinal::class,
                line: 12 + $i,
                pathname: sprintf('benchmarks/Fixture/Models/Model%d.php', $i),
                messageCustom: '',
            );
        }

        return $violations;
    }
}

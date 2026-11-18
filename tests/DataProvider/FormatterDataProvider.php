<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\DataProvider;

use Generator;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToBeClasses;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use StructuraPhp\Structura\ValueObjects\RuleValueObject;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

class FormatterDataProvider
{
    /**
     * @return Generator<string, array<int, AnalyseValueObject>>
     */
    public static function getAnalyseValueObject(): Generator
    {
        yield 'simple' => [
            new AnalyseValueObject(
                timeStart: 10,
                countPass: 10,
                countViolation: 10,
                countWarning: 1,
                countNotice: 1,
                analyseTestValueObjects: [
                    new AnalyseTestValueObject(
                        source: new SourceTestValueObject(
                            testClassname: 'TestAssert',
                            testDox: 'Asserts architecture rules',
                            methodName: '',
                            line: 0,
                            pathname: '',
                        ),
                        ruleValueObjects: [
                            new RuleValueObject(
                                raws: ['example.php' => 'Foo'],
                                finder: null,
                                that: (new AbstractExpr())
                                    ->addExpr(new ToBeClasses()),
                                except: null,
                                should: (new AbstractExpr()),
                            ),
                        ],
                        assertValueObject: new AssertValueObject(
                            pass: [
                                'to extend <promote>y</promote>' => 1,
                                'to be readonly' => 2,
                                'to be final' => 0,
                                'error notice' => 3,
                            ],
                            violations: [
                                'to be final' => [
                                    new ViolationValueObject(
                                        messageViolation: 'Resource <promote>x</promote> must be a final class',
                                        assertClassname: 'Foo',
                                        line: 1,
                                        pathname: 'example.php',
                                        messageCustom: '',
                                    ),
                                ],
                            ],
                            warnings: [
                                'to be readonly' => [
                                    '<promote>ToBeReadonly</promote> exception for <promote>x</promote> is no longer applicable',
                                ],
                            ],
                            notices: [
                                'error notice' => 'error notice',
                            ],
                        ),
                    ),
                ],
            ),
        ];
    }

    public static function makeOneViolation(
        float $timeStart = 0,
        string $pathname = 'example.php',
        string $message = 'message',
    ): AnalyseValueObject {
        return new AnalyseValueObject(
            timeStart: $timeStart,
            countPass: 0,
            countViolation: 1,
            countWarning: 0,
            countNotice: 0,
            analyseTestValueObjects: [
                new AnalyseTestValueObject(
                    source: new SourceTestValueObject(
                        testClassname: 'TestAssert',
                        testDox: 'Asserts architecture rules',
                        methodName: '',
                        line: 0,
                        pathname: '',
                    ),
                    ruleValueObjects: [],
                    assertValueObject: new AssertValueObject(
                        violations: [
                            'to be final' => [
                                new ViolationValueObject(
                                    messageViolation: $message,
                                    assertClassname: 'Foo',
                                    line: 1,
                                    pathname: $pathname,
                                    messageCustom: '',
                                ),
                            ],
                        ],
                    ),
                ),
            ],
        );
    }
}

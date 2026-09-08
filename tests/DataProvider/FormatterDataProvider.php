<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\DataProvider;

use Generator;
use StructuraPhp\Structura\Asserts\ToBeClasses;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use StructuraPhp\Structura\ValueObjects\RuleDescriptionValueObject;
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
                            textDox: 'Asserts architecture rules',
                            methodName: '',
                            line: 0,
                            pathname: '',
                        ),
                        ruleDescriptions: [
                            new RuleDescriptionValueObject(
                                sourceCount: 1,
                                fromFinder: false,
                                thatExpressions: [(string) new ToBeClasses()],
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
}

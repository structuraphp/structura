<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\DataProvider;

use Generator;
use StructuraPhp\Structura\AbstractExpr;
use StructuraPhp\Structura\Asserts\ToBeClasses;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use StructuraPhp\Structura\ValueObjects\RuleValuesObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

class FormatterDataProvider
{
    public static function getAnalyseValueObject(): Generator
    {
        yield 'simple' => [
            new AnalyseValueObject(
                timeStart: 10,
                countPass: 10,
                countViolation: 10,
                countWarning: 1,
                countNotice: 1,
                violationsByTests: [
                    [
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
                ],
                warningsByTests: [
                    [
                        'Foo' => [
                            '<promote>ToBeReadonly</promote> exception for <promote>x</promote> is no longer applicable',
                        ],
                    ],
                ],
                noticeByTests: [
                    [
                        'to be final' => 'error notice',
                    ],
                ],
                analyseTestValueObjects: [
                    new AnalyseTestValueObject(
                        textDox: 'Asserts architecture rules',
                        classname: 'TestAssert',
                        ruleValueObject: new RuleValuesObject(
                            raws: ['example.php' => 'Foo'],
                            finder: null,
                            that: (new AbstractExpr())
                                ->addExpr(new ToBeClasses()),
                            except: null,
                            should: (new AbstractExpr()),
                        ),
                        assertValueObject: new AssertValueObject(
                            pass: [
                                'to extend <promote>y</promote>' => 1,
                                'to be readonly' => 1,
                                'to be final' => 0,
                            ],
                            violations: [
                                'to be final' => [
                                    new ViolationValueObject(
                                        messageViolation: '',
                                        assertClassname: '',
                                        line: 0,
                                        pathname: '',
                                        messageCustom: '',
                                    ),
                                ],
                            ],
                            warnings: [
                                'to be readonly' => [
                                    1 => 'x',
                                ],
                            ],
                        ),
                    ),
                ],
            ),
        ];
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services\Parallel;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Exception\Console\WorkerProtocolException;
use StructuraPhp\Structura\Services\Parallel\AnalyseResultSerializer;
use StructuraPhp\Structura\ValueObjects\AnalyseTestValueObject;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;
use StructuraPhp\Structura\ValueObjects\AssertValueObject;
use StructuraPhp\Structura\ValueObjects\RuleDescriptionValueObject;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

#[CoversClass(AnalyseResultSerializer::class)]
final class AnalyseResultSerializerTest extends TestCase
{
    /**
     * A result must survive the trip through the NDJSON pipe unchanged, otherwise a parallel run
     * would silently report something different from a sequential one.
     */
    public function testRoundTripPreservesEveryOutcome(): void
    {
        $serializer = new AnalyseResultSerializer();
        $original = $this->analyseResult();

        $encoded = json_encode($serializer->toArray($original));
        self::assertIsString($encoded);

        /** @var array<array-key, mixed> $decoded */
        $decoded = json_decode($encoded, true);
        $restored = $serializer->fromArray($decoded, $original->timeStart);

        self::assertEquals($original, $restored);
    }

    public function testRoundTripKeepsViolationDetails(): void
    {
        $serializer = new AnalyseResultSerializer();

        $restored = $serializer->fromArray(
            $serializer->toArray($this->analyseResult()),
            10.0,
        );

        $violations = $restored->getViolations();
        self::assertArrayHasKey('to be final', $violations);
        self::assertSame(
            'Resource x must be a final class',
            $violations['to be final'][0]->messageViolation,
        );
        self::assertSame(12, $violations['to be final'][0]->line);
        self::assertSame('example.php', $violations['to be final'][0]->pathname);
    }

    /**
     * null and [] are different states: no that() clause at all versus an empty one, which the
     * progress formatter renders differently.
     */
    public function testRoundTripDistinguishesAbsentFromEmptyThatClause(): void
    {
        $serializer = new AnalyseResultSerializer();

        $restored = $serializer->fromArray(
            $serializer->toArray($this->analyseResult()),
            10.0,
        );

        $descriptions = $restored->analyseTestValueObjects[0]->ruleDescriptions;
        self::assertNull($descriptions[0]->thatExpressions);
        self::assertSame([], $descriptions[1]->thatExpressions);
        self::assertSame(['to be classes'], $descriptions[2]->thatExpressions);
    }

    public function testEmptyResultRoundTrips(): void
    {
        $serializer = new AnalyseResultSerializer();
        $empty = new AnalyseValueObject(0.0, 0, 0, 0, 0, []);

        $restored = $serializer->fromArray($serializer->toArray($empty), 0.0);

        self::assertEquals($empty, $restored);
    }

    public function testMalformedPayloadIsRejected(): void
    {
        $this->expectException(WorkerProtocolException::class);

        (new AnalyseResultSerializer())->fromArray(['tests' => ['not-an-array']], 0.0);
    }

    public function testNonIntegerCounterIsRejected(): void
    {
        $this->expectException(WorkerProtocolException::class);

        (new AnalyseResultSerializer())->fromArray(['countPass' => 'many', 'tests' => []], 0.0);
    }

    private function analyseResult(): AnalyseValueObject
    {
        return new AnalyseValueObject(
            timeStart: 10.0,
            countPass: 1,
            countViolation: 1,
            countWarning: 1,
            countNotice: 1,
            analyseTestValueObjects: [
                new AnalyseTestValueObject(
                    source: new SourceTestValueObject(
                        testClassname: 'Acme\Tests\TestFoo',
                        textDox: 'Foo architecture rules',
                        methodName: 'testFoo',
                        line: 21,
                        pathname: 'tests/TestFoo.php',
                    ),
                    ruleDescriptions: [
                        new RuleDescriptionValueObject(52, true),
                        new RuleDescriptionValueObject(2, false, []),
                        new RuleDescriptionValueObject(3, false, ['to be classes']),
                    ],
                    assertValueObject: new AssertValueObject(
                        pass: [
                            'to be readonly' => 1,
                            'to be final' => 0,
                            'to have prefix To' => 2,
                            'empty source' => 3,
                        ],
                        violations: [
                            'to be final' => [
                                new ViolationValueObject(
                                    messageViolation: 'Resource x must be a final class',
                                    assertClassname: 'Acme\Foo',
                                    line: 12,
                                    pathname: 'example.php',
                                    messageCustom: 'custom',
                                ),
                                new ViolationValueObject(
                                    messageViolation: 'Resource y must be a final class',
                                    assertClassname: 'Acme\Bar',
                                    line: 30,
                                    pathname: null,
                                    messageCustom: '',
                                ),
                            ],
                        ],
                        exceptions: ['to be final' => ['ignored class']],
                        warnings: ['to have prefix To' => ['exception no longer applicable']],
                        notices: ['empty source' => 'No PHP files found'],
                    ),
                ),
            ],
        );
    }
}

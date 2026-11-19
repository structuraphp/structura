<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\ValueObjects;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Tests\DataProvider\FormatterDataProvider;
use StructuraPhp\Structura\ValueObjects\AnalyseValueObject;

#[CoversClass(AnalyseValueObject::class)]
final class AnalyseValueObjectTest extends TestCase
{
    public function testWithBaselineSetsCountAndWarnings(): void
    {
        $result = FormatterDataProvider::makeOneViolation(timeStart: 12.5);

        $baselined = $result->withBaseline(5, ['stale']);

        self::assertSame(5, $baselined->countTolerated);
        self::assertSame(['stale'], $baselined->baselineWarnings);
        self::assertSame(12.5, $baselined->timeStart);
        self::assertSame(1, $baselined->countViolation);
        self::assertSame($result->analyseTestValueObjects, $baselined->analyseTestValueObjects);
        self::assertNull($result->countTolerated);
    }

    public function testGetWarningsIncludesBaselineWarnings(): void
    {
        $result = FormatterDataProvider::makeOneViolation()->withBaseline(0, ['stale entry']);

        self::assertSame(['baseline' => ['stale entry']], $result->getWarnings());
    }

    public function testGetWarningsKeepsTestWarningsBesideBaselineWarnings(): void
    {
        /** @var AnalyseValueObject $result */
        $result = iterator_to_array(FormatterDataProvider::getAnalyseValueObject())['simple'][0];

        self::assertSame(
            ['to be readonly', 'baseline'],
            array_keys($result->withBaseline(0, ['stale entry'])->getWarnings()),
        );
    }

    public function testGetWarningsMergesTheSameKeyAcrossTests(): void
    {
        /** @var AnalyseValueObject $result */
        $result = iterator_to_array(FormatterDataProvider::getAnalyseValueObject())['simple'][0];

        $warnings = AnalyseValueObject::merge(0, $result, $result)->getWarnings();

        self::assertCount(2, $warnings['to be readonly']);
    }

    public function testGetWarningsHasNoBaselineKeyWithoutStaleEntry(): void
    {
        self::assertSame([], FormatterDataProvider::makeOneViolation()->withBaseline(3, [])->getWarnings());
    }

    public function testMergeHasNoBaselineData(): void
    {
        $merged = AnalyseValueObject::merge(0, FormatterDataProvider::makeOneViolation()->withBaseline(5, ['stale']));

        self::assertNull($merged->countTolerated);
        self::assertSame([], $merged->baselineWarnings);
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToHaveMethod;
use StructuraPhp\Structura\Asserts\ToNotDependOn;
use StructuraPhp\Structura\Services\BaselineService;
use StructuraPhp\Structura\Tests\Helper\Sandbox;
use StructuraPhp\Structura\ValueObjects\BaselineEntryValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

#[CoversClass(BaselineService::class)]
#[CoversClass(BaselineEntryValueObject::class)]
final class BaselineServiceTest extends TestCase
{
    use Sandbox;

    private const BASE = '/home/a/project';

    private const DEPEND = 'Resource <promote>Foo</promote> must not depend on these namespaces A, B but depends on <fire>Legacy\Mailer</fire>';

    protected function setUp(): void
    {
        $this->enterSandbox();
    }

    protected function tearDown(): void
    {
        $this->leaveSandbox();
    }

    public function testIdentifierIsFireText(): void
    {
        $entry = BaselineEntryValueObject::fromViolation($this->violation(self::DEPEND), self::BASE);

        self::assertSame('Legacy\Mailer', $entry?->identifier);
        self::assertSame(ToNotDependOn::class, $entry->rule);
    }

    public function testIdentifierJoinsSeveralFireTags(): void
    {
        $entry = BaselineEntryValueObject::fromViolation(
            $this->violation('Resource <promote>Foo</promote> uses <fire>A</fire> and <fire>B</fire>'),
            self::BASE,
        );

        self::assertSame('A, B', $entry?->identifier);
    }

    public function testIdentifierIsStrippedMessageWithoutFire(): void
    {
        $entry = BaselineEntryValueObject::fromViolation(
            $this->violation('Resource <promote>Foo</promote> must have method <promote>__construct</promote>'),
            self::BASE,
        );

        self::assertSame('Resource Foo must have method __construct', $entry?->identifier);
    }

    /**
     * @param non-empty-string $pathname
     */
    #[DataProvider('getRelativePathProvider')]
    public function testEntryIsRelativeToTheBaseDirectory(string $pathname, string $base, string $expected): void
    {
        $entry = BaselineEntryValueObject::fromViolation($this->violation(self::DEPEND, $pathname), $base);

        self::assertSame($expected, $entry?->path);
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function getRelativePathProvider(): iterable
    {
        yield 'laptop checkout' => ['/home/a/project/src/Foo.php', '/home/a/project', 'src/Foo.php'];

        yield 'CI checkout' => ['/runner/work/project/src/Foo.php', '/runner/work/project', 'src/Foo.php'];

        yield 'outside the base directory' => ['/home/a/lib/Bar.php', '/home/a/project', '../lib/Bar.php'];
    }

    /**
     * @param non-empty-string $base
     */
    #[DataProvider('getCheckoutProvider')]
    public function testIdentifierStripsTheBaseDirectory(string $base): void
    {
        $include = BaselineEntryValueObject::fromViolation(
            $this->violation(\sprintf('Resource <promote>Foo</promote> uses path <fire>%s/vendor/autoload.php</fire>', $base), $base . '/src/Foo.php'),
            $base,
        );
        $corresponding = BaselineEntryValueObject::fromViolation(
            $this->violation(\sprintf('Resource name <promote>Foo</promote> must have corresponding file <promote>%s/tests/FooTest.php</promote>', $base), $base . '/src/Foo.php'),
            $base,
        );

        self::assertSame('vendor/autoload.php', $include?->identifier);
        self::assertSame('Resource name Foo must have corresponding file tests/FooTest.php', $corresponding?->identifier);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function getCheckoutProvider(): iterable
    {
        yield 'laptop' => ['/home/a/project'];

        yield 'CI' => ['/runner/work/project'];
    }

    public function testIdentifierKeepsGenericTypes(): void
    {
        $entry = BaselineEntryValueObject::fromViolation(
            $this->violation('Resource <promote>Foo</promote> must return <promote>array<string, int></promote>'),
            self::BASE,
        );

        self::assertSame('Resource Foo must return array<string, int>', $entry?->identifier);
    }

    public function testAnonymousRuleHasNoEntry(): void
    {
        $violation = $this->violation(self::DEPEND, rule: "class@anonymous\0/home/a/project/tests/Rule.php:5$0");

        self::assertNull(BaselineEntryValueObject::fromViolation($violation, self::BASE));
    }

    public function testFromFileRejectsInvalidPhp(): void
    {
        file_put_contents('baseline.php', '<?php return [');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Baseline file "baseline.php" is not valid PHP');

        BaselineService::fromFile('baseline.php', self::BASE);
    }

    public function testRawSourceHasNoEntry(): void
    {
        $violation = $this->violation(self::DEPEND, 'tmp/run_0.php');

        self::assertNull(BaselineEntryValueObject::fromViolation($violation, self::BASE));
        self::assertNull(BaselineEntryValueObject::fromViolation($this->violation(self::DEPEND, null), self::BASE));
        self::assertFalse($this->service()->isTolerated($violation));
    }

    public function testToleratesMatchingViolationWhateverLineAndCustomMessage(): void
    {
        $service = $this->service();

        self::assertTrue($service->isTolerated($this->violation(self::DEPEND, line: 3)));
        self::assertTrue($service->isTolerated($this->violation(self::DEPEND, line: 40, custom: 'x')));
    }

    public function testToleratesWhenRuleConfigurationChanged(): void
    {
        $violation = $this->violation(
            'Resource <promote>Foo</promote> must not depend on these namespaces A, B, C but depends on <fire>Legacy\Mailer</fire>',
        );

        self::assertTrue($this->service()->isTolerated($violation));
    }

    public function testDoesNotTolerateOtherPathRuleOrIdentifier(): void
    {
        $service = $this->service();

        self::assertFalse($service->isTolerated($this->violation(self::DEPEND, self::BASE . '/src/Other.php')));
        self::assertFalse($service->isTolerated($this->violation(self::DEPEND, rule: ToHaveMethod::class)));
        self::assertFalse($service->isTolerated($this->violation('depends on <fire>Legacy\Payment</fire>')));
        self::assertSame(0, $service->countTolerated());
    }

    public function testSameViolationIsToleratedTwice(): void
    {
        $service = $this->service();

        self::assertTrue($service->isTolerated($this->violation(self::DEPEND)));
        self::assertTrue($service->isTolerated($this->violation(self::DEPEND)));
        self::assertSame(2, $service->countTolerated());
        self::assertSame([], $service->getStaleWarnings());
    }

    public function testStaleWarningsListUnusedEntriesSorted(): void
    {
        $service = new BaselineService(
            [
                // the used entry (Mailer) comes first, and the stale ones are inserted unsorted (Zip before Mailer)
                'src/Foo.php' => [ToNotDependOn::class => ['Legacy\Mailer', 'Legacy\Zip']],
                'src/Zed.php' => [ToNotDependOn::class => ['Legacy\Mailer']],
            ],
            self::BASE,
        );

        $service->isTolerated($this->violation(self::DEPEND));

        self::assertSame(
            [
                \sprintf(
                    'Baseline entry <promote>Legacy\Mailer</promote> for <promote>%s</promote> in <promote>src/Zed.php</promote> no longer matches any violation',
                    ToNotDependOn::class,
                ),
                \sprintf(
                    'Baseline entry <promote>Legacy\Zip</promote> for <promote>%s</promote> in <promote>src/Foo.php</promote> no longer matches any violation',
                    ToNotDependOn::class,
                ),
            ],
            $service->getStaleWarnings(),
        );
    }

    public function testFromFileLoadsEntries(): void
    {
        file_put_contents(
            'baseline.php',
            \sprintf(
                "<?php return ['src/Foo.php' => [%s::class => ['Legacy\\\\Mailer']]];",
                '\\' . ToNotDependOn::class,
            ),
        );

        $service = BaselineService::fromFile($this->sandbox . '/baseline.php', self::BASE);

        self::assertTrue($service->isTolerated($this->violation(self::DEPEND)));
    }

    public function testFromFileRejectsMissingFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Baseline file "missing.php" not found');

        BaselineService::fromFile('missing.php', self::BASE);
    }

    #[DataProvider('getInvalidStructureProvider')]
    public function testFromFileRejectsInvalidStructure(string $content): void
    {
        file_put_contents('baseline.php', '<?php return ' . $content . ';');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Baseline file "baseline.php" must return array<string, array<class-string, list<string>>>',
        );

        BaselineService::fromFile('baseline.php', self::BASE);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function getInvalidStructureProvider(): iterable
    {
        yield 'not an array' => ["'nope'"];

        yield 'path without rules' => ["['src/Foo.php' => 'nope']"];

        yield 'rule without list' => ["['src/Foo.php' => ['Rule' => 'not-a-list']]"];

        yield 'identifier not a string' => ["['src/Foo.php' => ['Rule' => [1]]]"];

        yield 'numeric path' => ["[0 => ['Rule' => ['x']]]"];

        yield 'numeric rule' => ["['src/Foo.php' => [0 => ['x']]]"];
    }

    private function service(): BaselineService
    {
        return new BaselineService(
            ['src/Foo.php' => [ToNotDependOn::class => ['Legacy\Mailer']]],
            self::BASE,
        );
    }

    private function violation(
        string $message,
        ?string $pathname = self::BASE . '/src/Foo.php',
        string $rule = ToNotDependOn::class,
        int $line = 1,
        string $custom = '',
    ): ViolationValueObject {
        return new ViolationValueObject($message, $rule, $line, $pathname, $custom);
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use Countable;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use StructuraPhp\Structura\Asserts\ToBeFinal;
use StructuraPhp\Structura\Asserts\ToExtend;
use StructuraPhp\Structura\Asserts\ToImplement;
use StructuraPhp\Structura\Builder\AssertBuilder;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\ExprScript;
use StructuraPhp\Structura\Services\BaselineService;
use StructuraPhp\Structura\Services\ExecuteService;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\RuleValueObject;
use StructuraPhp\Structura\ValueObjects\SourceTestValueObject;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[CoversClass(ExecuteService::class)]
final class ExecuteServiceTest extends TestCase
{
    use ArchitectureAsserts;

    private const KEY = 'to extend <promote>Exception</promote>';

    private EventDispatcherInterface $dispatcher;

    private AssertBuilder $builder;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();

        $this->builder = new AssertBuilder();
        $this->dispatcher->addSubscriber($this->builder);
    }

    public function testPass(): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo extends \Exception {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toExtend(Exception::class),
            );

        $service = new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject());
        $service->assert();

        $result = $this->builder->getAssertValueObject();

        self::assertSame([self::KEY => 1], $result->pass);
        self::assertSame(1, $result->countAssertsSuccess());
        self::assertSame(0, $result->countAssertsFailure());
        self::assertSame(0, $result->countAssertsWarning());
        self::assertSame(0, $result->countWarning(self::KEY));
        self::assertSame(0, $result->countViolation(self::KEY));

        $violation = $result->violations;
        self::assertCount(0, $violation);

        $warning = $result->warnings;
        self::assertCount(0, $warning);
    }

    public function testViolation(): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toExtend(Exception::class),
            );

        $service = new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject());
        $service->assert();

        $result = $this->builder->getAssertValueObject();

        self::assertSame([self::KEY => 0], $result->pass);
        self::assertSame(0, $result->countAssertsSuccess());
        self::assertSame(1, $result->countAssertsFailure());
        self::assertSame(0, $result->countAssertsWarning());
        self::assertSame(0, $result->countWarning(self::KEY));
        self::assertSame(1, $result->countViolation(self::KEY));

        $violation = $result->violations[self::KEY][0] ?? null;

        self::assertInstanceOf(ViolationValueObject::class, $violation);
        self::assertSame(
            'Resource <promote>Foo</promote> must extend <promote>Exception</promote>',
            $violation->messageViolation,
        );
        self::assertSame(1, $violation->line);
        self::assertSame(ToExtend::class, $violation->assertClassname);
        self::assertSame('tmp/run_0.php', $violation->pathname);
        self::assertSame('', $violation->messageCustom);

        $warning = $result->warnings;
        self::assertCount(0, $warning);
    }

    /**
     * @param class-string $className
     */
    #[TestWith(['Foo'])]
    public function testException(string $className): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->except(
                static fn (Except $e): Except => $e
                    ->byClassname($className, ToExtend::class),
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toExtend(Exception::class),
            );

        $service = new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject());
        $service->assert();

        $result = $this->builder->getAssertValueObject();

        self::assertSame([self::KEY => 1], $result->pass);
        self::assertSame(1, $result->countAssertsSuccess());
        self::assertSame(0, $result->countAssertsFailure());
        self::assertSame(0, $result->countAssertsWarning());
        self::assertSame(0, $result->countWarning(self::KEY));
        self::assertSame(0, $result->countViolation(self::KEY));

        $violation = $result->violations;
        self::assertCount(0, $violation);

        $warning = $result->warnings;
        self::assertCount(0, $warning);
    }

    /**
     * @param class-string $className
     */
    #[TestWith(['Foo'])]
    public function testEveryExceptedFailureIsReported(string $className): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->except(
                static fn (Except $e): Except => $e
                    ->byClassname($className, ToExtend::class)
                    ->byClassname($className, ToBeFinal::class),
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toExtend(Exception::class)
                    ->toBeFinal(),
            );

        $service = new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject());
        $service->assert();

        self::assertCount(2, $this->builder->getAssertValueObject()->exceptions['Foo'] ?? []);
    }

    /**
     * @param class-string $className
     */
    #[TestWith(['Foo'])]
    public function testWarning(string $className): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo extends \Exception {}')
            ->except(
                static fn (Except $e): Except => $e
                    ->byClassname($className, ToExtend::class),
            )
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toExtend(Exception::class),
            );

        $service = new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject());
        $service->assert();

        $result = $this->builder->getAssertValueObject();

        self::assertSame([self::KEY => 2], $result->pass);
        self::assertSame(0, $result->countAssertsSuccess());
        self::assertSame(0, $result->countAssertsFailure());
        self::assertSame(1, $result->countAssertsWarning());
        self::assertSame(1, $result->countWarning(self::KEY));
        self::assertSame(0, $result->countViolation(self::KEY));

        $violation = $result->violations;
        self::assertCount(0, $violation);

        $warning = $result->warnings;
        self::assertSame(
            [
                self::KEY => [
                    sprintf(
                        '<promote>%s</promote> exception for <promote>%s</promote> is no longer applicable',
                        ToExtend::class,
                        'Foo',
                    ),
                ],
            ],
            $warning,
        );
    }

    public function testGlobalNoticeWhenDirectoryIsEmpty(): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromDir(dirname(__DIR__, 2) . '/Fixture/Empty')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toExtend(Exception::class),
            );

        $sourceTest = new SourceTestValueObject(
            testClassname: 'Exemple',
            testDox: 'my empty directory test',
            methodName: 'exemple',
            line: 0,
            pathname: __FILE__,
        );

        $service = new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject(), $sourceTest);
        $service->assert();

        $result = $this->builder->getAssertValueObject();

        self::assertSame(
            [
                'No PHP files found for test "<promote>Exemple</promote>". Assertions were skipped.' => 3,
                self::KEY => 1,
            ],
            $result->pass,
        );
        self::assertSame(1, $result->countAssertsSuccess());
        self::assertSame(0, $result->countAssertsFailure());
        self::assertSame(0, $result->countAssertsWarning());
        self::assertSame(1, $result->countAssertsNotices());
        self::assertSame([], $result->warnings);

        $noticeKey = 'No PHP files found for test "<promote>Exemple</promote>". Assertions were skipped.';
        self::assertSame(
            [$noticeKey => $noticeKey],
            $result->notices,
        );

        self::assertSame([], $result->violations);
    }

    public function testFullyToleratedRuleIsAPass(): void
    {
        $baseline = $this->baseline(['ToNotDependOn' => ['Legacy\Mailer', 'Legacy\Zip']]);

        (new ExecuteService($this->dispatcher, $this->dependingRule(), $this->baselineSource(), $baseline))->assert();

        $result = $this->builder->getAssertValueObject();
        self::assertSame([1], array_values($result->pass));
        self::assertSame([], $result->violations);
        self::assertSame(2, $baseline->countTolerated());
    }

    public function testBaselineIsIgnoredWithoutTheCurrentTest(): void
    {
        $baseline = $this->baseline(['ToNotDependOn' => ['Legacy\Mailer', 'Legacy\Zip']]);

        (new ExecuteService($this->dispatcher, $this->dependingRule(), null, $baseline))->assert();

        self::assertCount(2, array_merge(...array_values($this->builder->getAssertValueObject()->violations)));
        self::assertSame(0, $baseline->countTolerated());
    }

    public function testOnlyUntoleratedViolationsAreReported(): void
    {
        $baseline = $this->baseline(['ToNotDependOn' => ['Legacy\Mailer']]);

        (new ExecuteService($this->dispatcher, $this->dependingRule(), $this->baselineSource(), $baseline))->assert();

        $violations = array_merge(...array_values($this->builder->getAssertValueObject()->violations));
        self::assertCount(1, $violations);
        self::assertStringContainsString('<fire>Legacy\Zip</fire>', $violations[0]->messageViolation);
    }

    public function testOrGroupIsFilteredPerViolation(): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}', '/project/src/Foo.php')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->or(
                        static fn (Expr $assertion): Expr => $assertion
                            ->toExtend(Exception::class)
                            ->toExtend(ToExtend::class),
                    ),
            );
        $baseline = $this->baseline(['ToExtend' => ['Resource Foo must extend Exception']]);

        (new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject(), $this->baselineSource(), $baseline))->assert();

        $violations = array_merge(...array_values($this->builder->getAssertValueObject()->violations));
        self::assertCount(1, $violations);
        self::assertStringContainsString(ToExtend::class, $violations[0]->messageViolation);
    }

    public function testFailingRuleWithoutViolationIsStillAFailure(): void
    {
        $failing = new class implements ExprInterface {
            public function __toString(): string
            {
                return 'to fail silently';
            }

            public function assert(ClassDescription $class): bool
            {
                return false;
            }

            public function getViolation(ClassDescription $class): array
            {
                return [];
            }
        };

        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(static fn (Expr $assert): Expr => $assert->addExpr($failing));

        (new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject()))->assert();

        self::assertSame(['to fail silently' => 0], $this->builder->getAssertValueObject()->pass);
    }

    public function testScriptRuleIsToleratedInScriptMode(): void
    {
        $rulesBuilder = $this
            ->allScripts()
            ->fromRaw('<?php new \Legacy\Mailer();', '/project/src/Foo.php')
            ->should(static fn (ExprScript $assert): ExprScript => $assert->toNotDependOn(patterns: 'Legacy\.+'));
        $baseline = $this->baseline(['ToNotDependOn' => ['Legacy\Mailer']]);

        (new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject(), $this->baselineSource(), $baseline))->assert();

        $result = $this->builder->getAssertValueObject();
        self::assertSame([1], array_values($result->pass));
        self::assertSame(1, $baseline->countTolerated());
    }

    public function testOrGroupPassesWhenALaterBranchPasses(): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo extends \Exception {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->or(
                        static fn (Expr $assertion): Expr => $assertion
                            ->toExtend(ToExtend::class)
                            ->toExtend(Exception::class),
                    ),
            );

        (new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject()))->assert();

        self::assertSame([1], array_values($this->builder->getAssertValueObject()->pass));
    }

    /**
     * @param class-string $className
     */
    #[TestWith(['Foo'])]
    public function testOrGroupStopsAtFirstPassingBranch(string $className): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo extends \Exception {}')
            ->except(static fn (Except $e): Except => $e->byClassname($className, ToImplement::class))
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->or(
                        static fn (Expr $assertion): Expr => $assertion
                            ->toExtend(Exception::class)
                            ->toImplement(Countable::class),
                    ),
            );

        (new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject()))->assert();

        // the excepted second branch is never evaluated, so no exception is recorded
        self::assertSame([], $this->builder->getAssertValueObject()->exceptions);
    }

    public function testEmptyOrGroupPasses(): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo {}')
            ->should(static fn (Expr $assert): Expr => $assert->or(static fn (Expr $assertion): Expr => $assertion));

        (new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject()))->assert();

        self::assertSame([1], array_values($this->builder->getAssertValueObject()->pass));
    }

    public function testPathResolversAreInjectedInNestedGroups(): void
    {
        $rulesBuilder = $this
            ->allScripts()
            ->fromRaw('<?php require base_path() . "/vendor/autoload.php";')
            ->should(
                static fn (ExprScript $assert): ExprScript => $assert
                    ->or(
                        static fn (ExprScript $assertion): ExprScript => $assertion
                            ->toUseInclude(IncludeType::Require, '*/vendor/autoload.php'),
                    ),
            );
        $rulesBuilder->setPathResolvers(['base_path' => '/var/www']);

        (new ExecuteService($this->dispatcher, $rulesBuilder->getRuleObject()))->assert();

        self::assertSame([1], array_values($this->builder->getAssertValueObject()->pass));
    }

    /**
     * @param array<string, array<int, string>> $rules
     */
    private function baseline(array $rules): BaselineService
    {
        return new BaselineService(['Tests\TestBaseline::testRule' => ['src/Foo.php' => $rules]], '/project');
    }

    private function baselineSource(): SourceTestValueObject
    {
        return new SourceTestValueObject('Tests\TestBaseline', '', 'testRule', 0, '');
    }

    private function dependingRule(): RuleValueObject
    {
        return $this
            ->allClasses()
            ->fromRaw(
                '<?php class Foo { public function a(\Legacy\Mailer $m, \Legacy\Zip $z): void {} }',
                '/project/src/Foo.php',
            )
            ->should(static fn (Expr $assert): Expr => $assert->toNotDependOn(patterns: 'Legacy\.+'))
            ->getRuleObject();
    }
}

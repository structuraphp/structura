<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Asserts\ToExtend;
use StructuraPhp\Structura\Except;
use StructuraPhp\Structura\Expr;
use StructuraPhp\Structura\Services\ExecuteService;
use StructuraPhp\Structura\Tests\Helper\ArchitectureAsserts;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

#[CoversClass(ExecuteService::class)]
final class ExecuteServiceTest extends TestCase
{
    use ArchitectureAsserts;

    private const KEY = 'to extend <promote>Exception</promote>';

    public function testPass(): void
    {
        $rulesBuilder = $this
            ->allClasses()
            ->fromRaw('<?php class Foo extends \Exception {}')
            ->should(
                static fn (Expr $assert): Expr => $assert
                    ->toExtend(Exception::class),
            );

        $service = new ExecuteService($rulesBuilder->getRuleObject());
        $result = $service->assert();

        self::assertSame([self::KEY => 1], $result->getPass());
        self::assertSame(1, $result->countAssertsSuccess());
        self::assertSame(0, $result->countAssertsFailure());
        self::assertSame(0, $result->countAssertsWarning());
        self::assertSame(0, $result->countWarning(self::KEY));
        self::assertSame(0, $result->countViolation(self::KEY));

        $violation = $result->getViolations();
        self::assertEmpty($violation);

        $warning = $result->getWarnings();
        self::assertEmpty($warning);
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

        $service = new ExecuteService($rulesBuilder->getRuleObject());
        $result = $service->assert();

        self::assertSame([self::KEY => 0], $result->getPass());
        self::assertSame(0, $result->countAssertsSuccess());
        self::assertSame(1, $result->countAssertsFailure());
        self::assertSame(0, $result->countAssertsWarning());
        self::assertSame(0, $result->countWarning(self::KEY));
        self::assertSame(1, $result->countViolation(self::KEY));

        $violation = $result->getViolations()[self::KEY][0] ?? null;

        self::assertInstanceOf(ViolationValueObject::class, $violation);
        self::assertSame(
            'Resource <promote>Foo</promote> must extend by <promote>Exception</promote>',
            $violation->messageViolation,
        );
        self::assertSame(1, $violation->line);
        self::assertSame(ToExtend::class, $violation->assertClassname);
        self::assertNull($violation->pathname);
        self::assertSame('', $violation->messageCustom);

        $warning = $result->getWarnings();
        self::assertEmpty($warning);
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

        $service = new ExecuteService($rulesBuilder->getRuleObject());
        $result = $service->assert();

        self::assertSame([self::KEY => 1], $result->getPass());
        self::assertSame(1, $result->countAssertsSuccess());
        self::assertSame(0, $result->countAssertsFailure());
        self::assertSame(0, $result->countAssertsWarning());
        self::assertSame(0, $result->countWarning(self::KEY));
        self::assertSame(0, $result->countViolation(self::KEY));

        $violation = $result->getViolations();
        self::assertEmpty($violation);

        $warning = $result->getWarnings();
        self::assertEmpty($warning);
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

        $service = new ExecuteService($rulesBuilder->getRuleObject());
        $result = $service->assert();

        self::assertSame([self::KEY => 2], $result->getPass());
        self::assertSame(0, $result->countAssertsSuccess());
        self::assertSame(0, $result->countAssertsFailure());
        self::assertSame(1, $result->countAssertsWarning());
        self::assertSame(1, $result->countWarning(self::KEY));
        self::assertSame(0, $result->countViolation(self::KEY));

        $violation = $result->getViolations();
        self::assertEmpty($violation);

        $warning = $result->getWarnings();
        self::assertSame([self::KEY => ['Foo']], $warning);
    }
}

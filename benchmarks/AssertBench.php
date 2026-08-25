<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Benchmarks;

use Attribute;
use DateTimeImmutable;
use Generator;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use StructuraPhp\Structura\Asserts\DependsOnlyOn;
use StructuraPhp\Structura\Asserts\DependsOnlyOnAttribut;
use StructuraPhp\Structura\Asserts\DependsOnlyOnFunction;
use StructuraPhp\Structura\Asserts\DependsOnlyOnImplementation;
use StructuraPhp\Structura\Asserts\DependsOnlyOnInheritance;
use StructuraPhp\Structura\Asserts\DependsOnlyOnPhpDoc;
use StructuraPhp\Structura\Asserts\DependsOnlyOnUseTrait;
use StructuraPhp\Structura\Asserts\NotToBeInOneOfTheNamespaces;
use StructuraPhp\Structura\Asserts\ToBeAbstract;
use StructuraPhp\Structura\Asserts\ToBeAnonymousClasses;
use StructuraPhp\Structura\Asserts\ToBeAttribute;
use StructuraPhp\Structura\Asserts\ToBeBackedEnums;
use StructuraPhp\Structura\Asserts\ToBeClasses;
use StructuraPhp\Structura\Asserts\ToBeEnums;
use StructuraPhp\Structura\Asserts\ToBeFinal;
use StructuraPhp\Structura\Asserts\ToBeInOneOfTheNamespaces;
use StructuraPhp\Structura\Asserts\ToBeInterfaces;
use StructuraPhp\Structura\Asserts\ToBeReadonly;
use StructuraPhp\Structura\Asserts\ToBeTraits;
use StructuraPhp\Structura\Asserts\ToExtend;
use StructuraPhp\Structura\Asserts\ToExtendNothing;
use StructuraPhp\Structura\Asserts\ToHaveAnonymousClass;
use StructuraPhp\Structura\Asserts\ToHaveAttribute;
use StructuraPhp\Structura\Asserts\ToHaveConstant;
use StructuraPhp\Structura\Asserts\ToHaveCorresponding;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingClass;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingEnum;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingFile;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingInterface;
use StructuraPhp\Structura\Asserts\ToHaveCorrespondingTrait;
use StructuraPhp\Structura\Asserts\ToHaveFilePermission;
use StructuraPhp\Structura\Asserts\ToHaveMethod;
use StructuraPhp\Structura\Asserts\ToHaveNoAttribute;
use StructuraPhp\Structura\Asserts\ToHaveOnlyAttribute;
use StructuraPhp\Structura\Asserts\ToHavePrefix;
use StructuraPhp\Structura\Asserts\ToHaveSuffix;
use StructuraPhp\Structura\Asserts\ToImplement;
use StructuraPhp\Structura\Asserts\ToImplementNothing;
use StructuraPhp\Structura\Asserts\ToNotDependsOn;
use StructuraPhp\Structura\Asserts\ToNotDependsOnFunction;
use StructuraPhp\Structura\Asserts\ToNotDependsOnPhpDoc;
use StructuraPhp\Structura\Asserts\ToNotHaveAnonymousClass;
use StructuraPhp\Structura\Asserts\ToNotHaveConstant;
use StructuraPhp\Structura\Asserts\ToNotHaveCorrespondingFile;
use StructuraPhp\Structura\Asserts\ToNotUseInclude;
use StructuraPhp\Structura\Asserts\ToNotUseTrait;
use StructuraPhp\Structura\Asserts\ToOnlyImplement;
use StructuraPhp\Structura\Asserts\ToOnlyUseTrait;
use StructuraPhp\Structura\Asserts\ToReturnArray;
use StructuraPhp\Structura\Asserts\ToUseDeclare;
use StructuraPhp\Structura\Asserts\ToUseInclude;
use StructuraPhp\Structura\Asserts\ToUseTrait;
use StructuraPhp\Structura\Benchmarks\Fixture\Attributes\Cached;
use StructuraPhp\Structura\Benchmarks\Fixture\Attributes\Route;
use StructuraPhp\Structura\Benchmarks\Fixture\Concerns\HasTimestamps;
use StructuraPhp\Structura\Benchmarks\Fixture\Concerns\HasUuid;
use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\ControllerInterface;
use StructuraPhp\Structura\Benchmarks\Fixture\Contracts\JobInterface;
use StructuraPhp\Structura\Benchmarks\Fixture\Models\Model;
use StructuraPhp\Structura\Contracts\ExprInterface;
use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\Contracts\PathResolverAwareInterface;
use StructuraPhp\Structura\Enums\IncludeType;
use StructuraPhp\Structura\Enums\ScalarType;
use StructuraPhp\Structura\Enums\VisibilityType;
use StructuraPhp\Structura\ValueObjects\ClassDescription;

/**
 * Measures the cost of a single assertion over the whole corpus.
 *
 * The corpus is parsed once per iteration in the before method, so only
 * ExprInterface::assert() is timed, never the parser.
 */
#[BeforeMethods('setUp')]
#[Warmup(1)]
#[Revs(20)]
#[Iterations(5)]
final class AssertBench
{
    private const FIXTURE_NAMESPACE = 'StructuraPhp\Structura\Benchmarks\Fixture\\';

    /** @var array<int, ClassDescription> */
    private array $descriptions = [];

    private ExprInterface|ExprScriptInterface $assert;

    /**
     * @param array{assert: string} $params
     */
    public function setUp(array $params): void
    {
        $this->descriptions = Corpus::classDescriptions();
        $this->assert = self::asserts()[$params['assert']];

        if ($this->assert instanceof PathResolverAwareInterface) {
            $this->assert->setPathResolvers(['base_path' => Corpus::dir()]);
        }
    }

    #[ParamProviders('provideAsserts')]
    public function benchAssert(): void
    {
        foreach ($this->descriptions as $description) {
            $this->assert->assert($description);
        }
    }

    #[ParamProviders('provideAsserts')]
    public function benchViolation(): void
    {
        foreach ($this->descriptions as $description) {
            if ($this->assert->assert($description)) {
                continue;
            }

            $this->assert->getViolation($description);
        }
    }

    /**
     * @return Generator<string, array{assert: string}>
     */
    public static function provideAsserts(): Generator
    {
        foreach (array_keys(self::asserts()) as $name) {
            yield $name => ['assert' => $name];
        }
    }

    /**
     * Every assertion of src/Asserts, built with realistic arguments.
     *
     * @return array<string, ExprInterface|ExprScriptInterface>
     */
    private static function asserts(): array
    {
        return [
            'dependsOnlyOn' => new DependsOnlyOn(
                [DateTimeImmutable::class],
                [self::FIXTURE_NAMESPACE . '.+'],
            ),
            'dependsOnlyOnAttribut' => new DependsOnlyOnAttribut(
                [Cached::class, Route::class],
                ['Attribute'],
            ),
            'dependsOnlyOnFunction' => new DependsOnlyOnFunction(
                ['sprintf', 'count'],
                ['array_.+', 'str.+'],
            ),
            'dependsOnlyOnImplementation' => new DependsOnlyOnImplementation(
                [],
                [self::FIXTURE_NAMESPACE . 'Contracts\.+'],
            ),
            'dependsOnlyOnInheritance' => new DependsOnlyOnInheritance(
                ['RuntimeException', 'DomainException', 'InvalidArgumentException'],
                [self::FIXTURE_NAMESPACE . '.+'],
            ),
            'dependsOnlyOnPhpDoc' => new DependsOnlyOnPhpDoc(
                [],
                [self::FIXTURE_NAMESPACE . '.+'],
            ),
            'dependsOnlyOnUseTrait' => new DependsOnlyOnUseTrait(
                [],
                [self::FIXTURE_NAMESPACE . 'Concerns\.+'],
            ),
            'notToBeInOneOfTheNamespaces' => new NotToBeInOneOfTheNamespaces(
                ['App\.+', 'Illuminate\.+'],
            ),
            'toBeAbstract' => new ToBeAbstract(),
            'toBeAnonymousClasses' => new ToBeAnonymousClasses(),
            'toBeAttribute' => new ToBeAttribute(
                Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE,
            ),
            'toBeBackedEnums' => new ToBeBackedEnums(ScalarType::String),
            'toBeClasses' => new ToBeClasses(),
            'toBeEnums' => new ToBeEnums(),
            'toBeFinal' => new ToBeFinal(),
            'toBeInOneOfTheNamespaces' => new ToBeInOneOfTheNamespaces(
                [self::FIXTURE_NAMESPACE . '.+'],
            ),
            'toBeInterfaces' => new ToBeInterfaces(),
            'toBeReadonly' => new ToBeReadonly(),
            'toBeTraits' => new ToBeTraits(),
            'toExtend' => new ToExtend(Model::class),
            'toExtendNothing' => new ToExtendNothing(),
            'toHaveAnonymousClass' => new ToHaveAnonymousClass(),
            'toHaveAttribute' => new ToHaveAttribute(Cached::class),
            'toHaveConstant' => new ToHaveConstant(VisibilityType::Private),
            'toHaveCorresponding' => new ToHaveCorresponding(
                static fn (ClassDescription $classDescription): string => $classDescription
                    ->getResourceName(),
            ),
            'toHaveCorrespondingClass' => new ToHaveCorrespondingClass(
                static fn (ClassDescription $classDescription): string => str_replace(
                    'Dto',
                    'Models',
                    $classDescription->getResourceName(),
                ),
            ),
            'toHaveCorrespondingEnum' => new ToHaveCorrespondingEnum(
                static fn (ClassDescription $classDescription): string => str_replace(
                    'Models',
                    'Enums',
                    $classDescription->getResourceName(),
                ),
            ),
            'toHaveCorrespondingFile' => new ToHaveCorrespondingFile(
                static fn (ClassDescription $classDescription): string => $classDescription
                    ->getFileBasename(),
            ),
            'toHaveCorrespondingInterface' => new ToHaveCorrespondingInterface(
                static fn (ClassDescription $classDescription): string => $classDescription
                    ->getResourceName() . 'Interface',
            ),
            'toHaveCorrespondingTrait' => new ToHaveCorrespondingTrait(
                static fn (ClassDescription $classDescription): string => str_replace(
                    'Models',
                    'Concerns',
                    $classDescription->getResourceName(),
                ),
            ),
            'toHaveFilePermission' => new ToHaveFilePermission('0644'),
            'toHaveMethod' => new ToHaveMethod('__invoke'),
            'toHaveNoAttribute' => new ToHaveNoAttribute(),
            'toHaveOnlyAttribute' => new ToHaveOnlyAttribute(Cached::class),
            'toHavePrefix' => new ToHavePrefix('Order'),
            'toHaveSuffix' => new ToHaveSuffix('Controller'),
            'toImplement' => new ToImplement(ControllerInterface::class),
            'toImplementNothing' => new ToImplementNothing(''),
            'toNotDependsOn' => new ToNotDependsOn(
                [DateTimeImmutable::class],
                ['Symfony\.+'],
            ),
            'toNotDependsOnFunction' => new ToNotDependsOnFunction(
                ['dd', 'dump', 'var_dump'],
                ['eval.*'],
            ),
            'toNotDependsOnPhpDoc' => new ToNotDependsOnPhpDoc(
                [],
                ['Illuminate\.+'],
            ),
            'toNotHaveAnonymousClass' => new ToNotHaveAnonymousClass(),
            'toNotHaveConstant' => new ToNotHaveConstant(VisibilityType::Public),
            'toNotHaveCorrespondingFile' => new ToNotHaveCorrespondingFile(
                static fn (ClassDescription $classDescription): string => $classDescription
                    ->getFileBasename() . '.bak',
            ),
            'toNotUseInclude' => new ToNotUseInclude(),
            'toNotUseTrait' => new ToNotUseTrait(),
            'toOnlyImplement' => new ToOnlyImplement(JobInterface::class),
            'toOnlyUseTrait' => new ToOnlyUseTrait(HasUuid::class),
            'toReturnArray' => new ToReturnArray(),
            'toUseDeclare' => new ToUseDeclare('strict_types', '1'),
            'toUseInclude' => new ToUseInclude(IncludeType::RequireOnce),
            'toUseTrait' => new ToUseTrait(HasTimestamps::class),
        ];
    }
}

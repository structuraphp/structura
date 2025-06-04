<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use StructuraPhp\Structura\ValueObjects\AnalyseSutValueObject;
use StructuraPhp\Structura\ValueObjects\RootNamespaceValueObject;
use StructuraPhp\Structura\ValueObjects\RuleSutValuesObject;
use Symfony\Component\Finder\Finder;

/**
 * Directory mirror the package and class structure of the System Under Test (SUT).
 *
 * @see https://docs.phpunit.de/en/12.0/organizing-tests.html
 */
class SutService
{
    public function __construct(
        private readonly RuleSutValuesObject $ruleSutValuesObject,
    ) {}

    public function analyse(): AnalyseSutValueObject
    {
        $rule = $this->ruleSutValuesObject;
        $appClassesName = $this->getFiles($rule->appRootNamespace);
        $testClassesName = $this->getFiles($rule->testRootNamespace);

        $namespaces = [];

        foreach ($testClassesName as $testClass) {
            if (\in_array($testClass, $rule->expects, true)) {
                continue;
            }

            $namespace = str_replace(
                $rule->testRootNamespace->namespace,
                $rule->appRootNamespace->namespace,
                $testClass,
            );

            $namespaceTest = $this->lStrReplace('Test', '', $namespace);
            if ($namespaceTest !== $testClass) {
                $namespaces[$namespaceTest] = $testClass;
            }
        }

        $violations = array_diff_key(
            $namespaces,
            array_flip($appClassesName),
        );

        return new AnalyseSutValueObject(
            countPass: \count($testClassesName) - \count($violations),
            countViolation: \count($violations),
            violations: $violations,
        );
    }

    private function lStrReplace(string $search, string $replace, string $subject): string
    {
        $pos = strrpos($subject, $search);

        if (\is_int($pos)) {
            return substr_replace($subject, $replace, $pos, \strlen($search));
        }

        return $subject;
    }

    /**
     * @return array<int,string>
     */
    private function getFiles(RootNamespaceValueObject $root): array
    {
        $classesName = [];

        $finder = Finder::create()
            ->files()
            ->followLinks()
            ->sortByName()
            ->in($root->directory);

        foreach ($finder as $file) {
            $pathName = $file->getPath();

            if (str_starts_with($pathName, $root->directory)) {
                $className = str_replace(
                    [$root->directory, '/'],
                    [$root->namespace, '\\'],
                    $pathName,
                );
                $className .= '\\' . pathinfo($file->getPathname(), \PATHINFO_FILENAME);

                if (
                    class_exists($className)
                    || trait_exists($className)
                    || interface_exists($className)
                    || enum_exists($className)
                ) {
                    $classesName[] = $className;
                }
            }
        }

        return $classesName;
    }
}

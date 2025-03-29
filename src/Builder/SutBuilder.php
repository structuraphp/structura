<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Builder;

use LogicException;
use StructuraPhp\Structura\ValueObjects\RootNamespaceValueObject;
use StructuraPhp\Structura\ValueObjects\RuleSutValuesObject;

class SutBuilder
{
    private ?RootNamespaceValueObject $appRootNamespace = null;

    private ?RootNamespaceValueObject $testRootNamespace = null;

    /** @var null|array<int, class-string> */
    private ?array $expects = null;

    public function appRootNamespace(string $namespace, string $directory): self
    {
        $this->appRootNamespace = new RootNamespaceValueObject(
            namespace: $namespace,
            directory: $directory,
        );

        return $this;
    }

    public function testRootNamespace(string $namespace, string $directory): self
    {
        $this->testRootNamespace = new RootNamespaceValueObject(
            namespace: $namespace,
            directory: $directory,
        );

        return $this;
    }

    /**
     * @param class-string $className
     */
    public function expect(string $className): self
    {
        $this->expects[] = $className;

        return $this;
    }

    public function getRuleSutValueObject(): RuleSutValuesObject
    {
        if (!$this->appRootNamespace instanceof RootNamespaceValueObject) {
            throw new LogicException('');
        }

        if (!$this->testRootNamespace instanceof RootNamespaceValueObject) {
            throw new LogicException('');
        }

        return new RuleSutValuesObject(
            appRootNamespace: $this->appRootNamespace,
            testRootNamespace: $this->testRootNamespace,
            expects: $this->expects ?? [],
        );
    }
}

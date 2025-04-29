<?php

declare(strict_types=1);

namespace Structura\Services;

use RuntimeException;
use Structura\Configs\StructuraConfig;
use Structura\ValueObjects\GenerateTestValueObject;
use Structura\ValueObjects\MakeTestValueObject;
use Structura\ValueObjects\RootNamespaceValueObject;
use Symfony\Component\Filesystem\Filesystem;

class MakeTestService
{
    private const STUB_FILENAME = '%s/Stubs/test.php.dist';

    public function __construct(
        private StructuraConfig $structuraConfig,
    ) {}

    public function make(MakeTestValueObject $dto): GenerateTestValueObject
    {
        $rootNamespace = $this->structuraConfig->getArchiRootNamespace();
        if (!$rootNamespace instanceof RootNamespaceValueObject) {
            throw new RuntimeException('Root namespace not found');
        }

        $className = str_starts_with($dto->testClassName, 'Test')
            ? $dto->testClassName
            : 'Test' . $dto->testClassName;

        $content = str_replace(
            [
                '{{ namespace }}',
                '{{ class }}',
                '{{ path }}',
            ],
            [
                $rootNamespace->namespace,
                $className,
                $dto->path,
            ],
            (string) file_get_contents(
                \sprintf(self::STUB_FILENAME, \dirname(__DIR__)),
            ),
        );

        return new GenerateTestValueObject(
            content: $content,
            filename: \sprintf(
                '%s/%s/%s.php',
                getcwd(),
                $rootNamespace->directory,
                $className,
            ),
            className: $className,
        );
    }

    public function generate(GenerateTestValueObject $makeValueObject): void
    {
        if (file_exists($makeValueObject->filename)) {
            throw new RuntimeException(
                sprintf('File %s already exists', $makeValueObject->filename),
            );
        }

        (new Filesystem())
            ->dumpFile($makeValueObject->filename, $makeValueObject->content);
    }
}

<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Services;

use InvalidArgumentException;
use RuntimeException;
use StructuraPhp\Structura\Concerns\Pipe;
use StructuraPhp\Structura\Configs\StructuraConfig;
use StructuraPhp\Structura\ValueObjects\GenerateTestValueObject;
use StructuraPhp\Structura\ValueObjects\MakeTestValueObject;
use StructuraPhp\Structura\ValueObjects\RootNamespaceValueObject;
use Symfony\Component\Filesystem\Filesystem;

final readonly class MakeTestService
{
    use Pipe;

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

        $parts = explode('\\', $dto->testClassName);
        $className = array_pop($parts);

        $content = str_replace(
            [
                '{{ namespace }}',
                '{{ class }}',
                '{{ path }}',
            ],
            [
                implode('\\', [$rootNamespace->namespace, ...$parts]),
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
                '%s/%s.php',
                (string) getcwd(),
                implode('/', [$rootNamespace->directory, ...$parts, $className]),
            ),
        );
    }

    public function generate(GenerateTestValueObject $makeValueObject): void
    {
        (new Filesystem())
            ->dumpFile($makeValueObject->filename, $makeValueObject->content);
    }

    public function getNamespace(mixed $input): string
    {
        if (!is_string($input) || $input === '') {
            throw new RuntimeException('The name of the test class is required.');
        }

        /** @var callable(string): string $replace */
        $replace = $this->pipe(
            static fn (string $str): string => str_replace(['\\', '/'], ['\\', '\\'], $str),
            static fn (string $str): string => preg_replace('/\\\+/', '\\', $str) ?? '',
            static fn (string $str): string => trim($str, "\t\n\r\v\0\x0B"),
            static fn (string $str): string => trim($str, '\\'),
            static fn (string $str): string => preg_replace('/([^\\\]+)$/', 'Test$1', $str) ?? '',
        );

        $namespace = $replace($input);

        if (!preg_match('/^(([A-Z]+[a-z0-9]*)+\\\?)+$/n', $namespace)) {
            throw new RuntimeException(
                'The name of the test class must be PSR4-compliant.',
            );
        }

        $file = sprintf(
            '%s/%s/%s.php',
            (string) getcwd(),
            $this->structuraConfig->getArchiRootNamespace()->directory ?? '',
            str_replace('\\', DIRECTORY_SEPARATOR, $namespace),
        );

        if (file_exists($file)) {
            throw new RuntimeException(
                sprintf('File %s already exists', $file),
            );
        }

        return $namespace;
    }

    public function getPath(mixed $input): string
    {
        if (!is_string($input) || $input === '') {
            throw new InvalidArgumentException('Source code path is required.');
        }

        /** @var callable(string): string $replace */
        $replace = $this->pipe(
            static fn (string $str): string => str_replace(['\\', '/'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $str),
            static fn (string $str): string => preg_replace('/\/+/', DIRECTORY_SEPARATOR, $str) ?? '',
            static fn (string $str): string => trim($str, "\t\n\r\v\0\x0B"),
        );

        $path = $replace($input);

        if (!is_dir($path)) {
            throw new RuntimeException(
                'The source code path does not exist.',
            );
        }

        return $path;
    }
}

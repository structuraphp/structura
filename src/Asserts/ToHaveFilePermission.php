<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ClassDescription;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToHaveFilePermission implements ExprScriptInterface
{
    public function __construct(
        private string $expectedPermission,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to have file permission <promote>%s</promote>', $this->expectedPermission);
    }

    public function assert(ScriptDescription $description): bool
    {
        if (!file_exists($description->getFileBasename())) {
            return true;
        }

        $perms = fileperms($description->getFileBasename());
        if ($perms === false) {
            return true;
        }

        $actualPermission = \substr(\decoct($perms), -4);

        return $actualPermission === $this->expectedPermission;
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ScriptDescription $description): array
    {
        $filename = $description->getFileBasename();
        $actualPermission = 'unknown';

        if (file_exists($filename)) {
            $perms = fileperms($filename);
            if ($perms !== false) {
                $actualPermission = \substr(\decoct($perms), -4);
            }
        }

        $resourceName = $description instanceof ClassDescription
            ? $description->getResourceName()
            : $filename;

        return [
            new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must have file permission <promote>%s</promote> but is <fire>%s</fire>',
                    $resourceName,
                    $this->expectedPermission,
                    $actualPermission,
                ),
                $this::class,
                $description instanceof ClassDescription
                    ? $description->lines
                    : 0,
                $filename,
                $this->message,
            ),
        ];
    }
}

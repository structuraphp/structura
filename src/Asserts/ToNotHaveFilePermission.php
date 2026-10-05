<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Asserts;

use StructuraPhp\Structura\Contracts\ExprScriptInterface;
use StructuraPhp\Structura\ValueObjects\ScriptDescription;
use StructuraPhp\Structura\ValueObjects\ViolationValueObject;

final readonly class ToNotHaveFilePermission implements ExprScriptInterface
{
    public function __construct(
        private string $forbiddenPermission,
        private string $message = '',
    ) {}

    public function __toString(): string
    {
        return \sprintf('to not have file permission <promote>%s</promote>', $this->forbiddenPermission);
    }

    public function assert(ScriptDescription $description): bool
    {
        return $this->getPermission($description->getFileBasename()) !== $this->forbiddenPermission;
    }

    /**
     * @return array<int, ViolationValueObject>
     */
    public function getViolation(ScriptDescription $description): array
    {
        $filename = $description->getFileBasename();

        return [
            new ViolationValueObject(
                \sprintf(
                    'Resource <promote>%s</promote> must not have file permission <fire>%s</fire>',
                    $filename,
                    $this->forbiddenPermission,
                ),
                $this::class,
                0,
                $filename,
                $this->message,
            ),
        ];
    }

    private function getPermission(string $filename): ?string
    {
        if (!file_exists($filename)) {
            return null;
        }

        $perms = fileperms($filename);

        return $perms === false ? null : \substr(\decoct($perms), -4);
    }
}

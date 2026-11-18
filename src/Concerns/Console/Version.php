<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Concerns\Console;

use Composer\InstalledVersions;

trait Version
{
    private function getInfos(
        string $configPath = 'undefined',
        ?string $projectVersion = null,
        ?string $phpVersion = null,
    ): string {
        return sprintf(
            <<<'INFO'
            Version: %-5s Structura %s
            Runtime: %-5s PHP %s
            Configuration: %s
            INFO,
            '',
            $projectVersion ?? $this->getProjectVersion(),
            '',
            $phpVersion ?? $this->getPhpVersion(),
            $configPath,
        );
    }

    /**
     * @param string $gitTag replaced by the git tag in phar builds (box.json "git-tag")
     */
    private function getProjectVersion(string $gitTag = '@git-tag@'): string
    {
        return $gitTag !== implode('', ['@', 'git-tag@'])
            ? $gitTag
            : InstalledVersions::getVersion('structuraphp/structura') ?? 'UNKNOWN';
    }

    private function getPhpVersion(): string
    {
        return PHP_VERSION;
    }
}

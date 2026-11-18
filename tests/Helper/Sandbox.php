<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Helper;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Runs a test in its own working directory, so tests writing files never
 * collide when Infection runs them in parallel processes.
 */
trait Sandbox
{
    private string $sandbox = '';

    private string $workingDirectory = '';

    private function enterSandbox(): void
    {
        $this->workingDirectory = (string) getcwd();
        $this->sandbox = sys_get_temp_dir() . '/structura_' . bin2hex(random_bytes(8));

        mkdir($this->sandbox);
        chdir($this->sandbox);
    }

    private function leaveSandbox(): void
    {
        chdir($this->workingDirectory);
        (new Filesystem())->remove($this->sandbox);
    }
}

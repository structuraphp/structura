<?php

declare(strict_types=1);

namespace StructuraPhp\Structura\Tests\Unit\Services;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use StructuraPhp\Structura\Services\AutoloadService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(AutoloadService::class)]
final class AutoloadServiceTest extends TestCase
{
    private string $testDir;

    private BufferedOutput $buffer;

    private SymfonyStyle $output;

    protected function setUp(): void
    {
        $this->testDir = \sys_get_temp_dir() . '/structura_test_' . uniqid();
        @mkdir($this->testDir, 0755, true);

        $this->buffer = new BufferedOutput();
        $this->output = new SymfonyStyle(new ArrayInput([]), $this->buffer);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->testDir);
        unset($GLOBALS['structura_autoload_loaded']);
    }

    public function testLoadRequiresAutoloadWithoutMessage(): void
    {
        $autoload = $this->testDir . '/autoload.php';
        file_put_contents($autoload, '<?php $GLOBALS[\'structura_autoload_loaded\'] = true;');

        (new AutoloadService())->load($autoload, $this->output);

        self::assertTrue($GLOBALS['structura_autoload_loaded'] ?? false);
        self::assertSame('', $this->buffer->fetch());
    }

    public function testLoadWithMissingFileDisplaysError(): void
    {
        $autoload = $this->testDir . '/missing.php';

        (new AutoloadService())->load($autoload, $this->output);

        $display = $this->buffer->fetch();
        self::assertStringContainsString('[ERROR]', $display);
        self::assertStringContainsString('The autoload file', $display);
    }

    public function testLoadWithoutConfigurationDisplaysWarning(): void
    {
        (new AutoloadService())->load(null, $this->output);

        $display = (string) preg_replace('/\s+/', ' ', $this->buffer->fetch());
        self::assertStringContainsString(
            '[WARNING] Running inside a PHAR archive without autoload configuration: '
            . 'custom rules and formatters cannot be loaded. '
            . 'Set it in structura.php, for example: $config->setAutoload(__DIR__ . "/vendor/autoload.php").',
            $display,
        );
    }
}

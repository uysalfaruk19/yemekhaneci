<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\RateLimiter;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/yh_test_rl_' . uniqid();
        mkdir($this->tmpDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmpDir)) {
            foreach (glob($this->tmpDir . '/*') ?: [] as $f) @unlink($f);
            @rmdir($this->tmpDir);
        }
    }

    public function test_limit_alti_allow_true(): void
    {
        $rl = new RateLimiter('alpha', 3, 60, $this->tmpDir);
        $this->assertTrue($rl->allow('1.2.3.4'));
        $this->assertTrue($rl->allow('1.2.3.4'));
        $this->assertTrue($rl->allow('1.2.3.4'));
    }

    public function test_limit_asiminda_allow_false(): void
    {
        $rl = new RateLimiter('beta', 2, 60, $this->tmpDir);
        $this->assertTrue($rl->allow('5.5.5.5'));
        $this->assertTrue($rl->allow('5.5.5.5'));
        $this->assertFalse($rl->allow('5.5.5.5'));
    }

    public function test_anahtarlar_bagimsiz(): void
    {
        $rl = new RateLimiter('gamma', 1, 60, $this->tmpDir);
        $this->assertTrue($rl->allow('a'));
        $this->assertTrue($rl->allow('b'));
        $this->assertFalse($rl->allow('a'));
    }

    public function test_remaining_dogru_hesaplanir(): void
    {
        $rl = new RateLimiter('delta', 5, 60, $this->tmpDir);
        $rl->allow('x');
        $rl->allow('x');
        $this->assertSame(3, $rl->remaining('x'));
    }

    public function test_reset_sayaci_sifirlar(): void
    {
        $rl = new RateLimiter('eps', 2, 60, $this->tmpDir);
        $rl->allow('z');
        $rl->allow('z');
        $this->assertFalse($rl->allow('z'));
        $rl->reset('z');
        $this->assertTrue($rl->allow('z'));
    }

    public function test_path_traversal_sanitize(): void
    {
        $rl = new RateLimiter('../../etc/passwd', 1, 60, $this->tmpDir);
        $rl->allow('test');
        $files = array_values(array_filter(
            scandir($this->tmpDir) ?: [],
            static fn(string $f) => $f !== '.' && $f !== '..'
        ));
        foreach ($files as $f) {
            $this->assertStringNotContainsString('..', $f);
            $this->assertStringNotContainsString('/', $f);
        }
    }
}

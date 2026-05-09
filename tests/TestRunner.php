<?php

declare(strict_types=1);

namespace Tests;

/**
 * Composer + PHPUnit gelmeden önce kullanılan minik test runner (Faz 0.5.15).
 * Faz 1.0a'da PHPUnit ile değiştirilecek; testler korunup `phpunit/phpunit`
 * tarafından çalıştırılabilecek formatta yazıldı.
 *
 * Kullanım:  php tests/TestRunner.php
 */
final class TestRunner
{
    public static int $assertions = 0;
    public static int $failures = 0;
    /** @var array<int, array{name:string, status:string, message:?string, time:float}> */
    public static array $results = [];

    public static function assertTrue(bool $condition, string $message = ''): void
    {
        self::$assertions++;
        if (!$condition) {
            throw new \AssertionError($message !== '' ? $message : 'Beklenen true, gerçek false.');
        }
    }

    public static function assertFalse(bool $condition, string $message = ''): void
    {
        self::assertTrue(!$condition, $message);
    }

    public static function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
    {
        self::$assertions++;
        if ($expected != $actual) {
            $msg = $message !== '' ? $message : '';
            throw new \AssertionError(sprintf(
                "%sBeklenen: %s\nGerçek: %s",
                $msg ? $msg . "\n" : '',
                var_export($expected, true),
                var_export($actual, true)
            ));
        }
    }

    public static function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        self::$assertions++;
        if ($expected !== $actual) {
            throw new \AssertionError(sprintf(
                "%sBeklenen (===): %s\nGerçek: %s",
                $message ? $message . "\n" : '',
                var_export($expected, true),
                var_export($actual, true)
            ));
        }
    }

    public static function assertEqualsWithDelta(float $expected, float $actual, float $delta, string $message = ''): void
    {
        self::$assertions++;
        if (abs($expected - $actual) > $delta) {
            throw new \AssertionError(sprintf(
                "%sBeklenen ≈ %f (delta %f), gerçek %f (fark %f)",
                $message ? $message . "\n" : '',
                $expected, $delta, $actual, abs($expected - $actual)
            ));
        }
    }

    public static function assertContains(string $needle, string $haystack, string $message = ''): void
    {
        self::$assertions++;
        if (!str_contains($haystack, $needle)) {
            throw new \AssertionError(sprintf(
                "%s'%s' parçası bulunamadı.",
                $message ? $message . "\n" : '',
                $needle
            ));
        }
    }

    public static function assertThrows(string $expectedExceptionClass, callable $fn, string $message = ''): void
    {
        self::$assertions++;
        try {
            $fn();
        } catch (\Throwable $e) {
            if (!($e instanceof $expectedExceptionClass)) {
                throw new \AssertionError(sprintf(
                    "%sBeklenen %s, atılan %s: %s",
                    $message ? $message . "\n" : '',
                    $expectedExceptionClass,
                    get_class($e),
                    $e->getMessage()
                ));
            }
            return;
        }
        throw new \AssertionError(($message ? $message . "\n" : '') . "Hiç istisna atılmadı (beklenen: {$expectedExceptionClass}).");
    }

    public static function run(string $name, callable $test): void
    {
        $start = microtime(true);
        try {
            $test();
            self::$results[] = ['name' => $name, 'status' => 'PASS', 'message' => null, 'time' => microtime(true) - $start];
            echo "  \033[32m✓\033[0m {$name}\n";
        } catch (\Throwable $e) {
            self::$failures++;
            self::$results[] = ['name' => $name, 'status' => 'FAIL', 'message' => $e->getMessage(), 'time' => microtime(true) - $start];
            echo "  \033[31m✗ {$name}\033[0m\n    " . str_replace("\n", "\n    ", $e->getMessage()) . "\n";
        }
    }

    public static function group(string $title, callable $tests): void
    {
        echo "\n\033[1;36m▸ {$title}\033[0m\n";
        $tests();
    }

    public static function summary(): int
    {
        $total = count(self::$results);
        $passed = $total - self::$failures;
        echo "\n";
        echo str_repeat('─', 60) . "\n";
        if (self::$failures === 0) {
            echo "\033[1;32m✓ Tüm testler geçti\033[0m — {$passed}/{$total} test, " . self::$assertions . " assertion\n";
            return 0;
        }
        echo "\033[1;31m✗ Başarısız\033[0m — {$passed} geçti, " . self::$failures . " başarısız (toplam {$total})\n";
        return 1;
    }
}

// ---- Bootstrap ----
require __DIR__ . '/../app/Helpers/functions.php';
spl_autoload_register(static function (string $class): void {
    $base = dirname(__DIR__);
    if (str_starts_with($class, 'App\\')) {
        $relative = str_replace(['App\\', '\\'], ['', '/'], $class);
        $file = $base . '/app/' . $relative . '.php';
        if (is_file($file)) require $file;
    } elseif (str_starts_with($class, 'Tests\\')) {
        $relative = str_replace(['Tests\\', '\\'], ['', '/'], $class);
        $file = $base . '/tests/' . $relative . '.php';
        if (is_file($file)) require $file;
    }
});

// Tüm test dosyalarını çalıştır
$testFiles = glob(__DIR__ . '/Unit/*Test.php') ?: [];
sort($testFiles);
foreach ($testFiles as $file) {
    require_once $file;
}

exit(TestRunner::summary());

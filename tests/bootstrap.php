<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap — composer autoload + helpers + Feature server başlatma.
 */

require __DIR__ . '/../vendor/autoload.php';

if (!function_exists('app_path')) {
    require __DIR__ . '/../app/Helpers/functions.php';
}

// Feature suite çalışıyorsa arka plan PHP server başlat (Unit testlerde gerekmez).
$argv = $_SERVER['argv'] ?? [];
$runningFeature = false;
foreach ($argv as $arg) {
    if (str_contains($arg, 'Feature') || str_contains($arg, 'tests/Feature')) {
        $runningFeature = true;
        break;
    }
}
// Default suite (no --testsuite flag) tüm testleri çalıştırır → server başlat
$hasTestsuite = in_array('--testsuite', $argv, true);
if (!$hasTestsuite || $runningFeature) {
    // Test izolasyonu: state dosyalarını temizle
    $rlDir = __DIR__ . '/../storage/ratelimit';
    if (is_dir($rlDir)) {
        foreach (glob($rlDir . '/*.txt') ?: [] as $f) @unlink($f);
    }
    @unlink(__DIR__ . '/../storage/applications/suppliers.json');
    @unlink(__DIR__ . '/../storage/quotes/quick_quotes.json');
    @unlink(__DIR__ . '/../storage/auth/totp.json');
    @unlink(__DIR__ . '/../storage/inflation/calculations.json');

    \Tests\Feature\TestServer::start();
}

<?php

declare(strict_types=1);

namespace App\Controllers\Public;

/**
 * Health check endpoint'leri (Hostinger / Traefik / Kubernetes uyumlu).
 *
 * GET /saglik  — liveness  (uygulama çalışıyor mu, lightweight)
 * GET /hazir   — readiness (storage yazılabilir mi, vendor mevcut mu, DB Faz 1+)
 */
final class HealthController
{
    public function liveness(): string
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        return json_encode([
            'status'    => 'ok',
            'timestamp' => date('c'),
            'uptime_s'  => self::uptime(),
        ]) ?: '{}';
    }

    public function readiness(): string
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $checks = [
            'php_version'         => self::checkPhpVersion(),
            'composer_autoload'   => self::checkComposerAutoload(),
            'storage_writable'    => self::checkStorageWritable(),
            'sessions_writable'   => self::checkSessionsWritable(),
            'config_present'      => self::checkConfigPresent(),
            'inflation_data'      => self::checkInflationData(),
        ];

        $ok = !in_array(false, array_column($checks, 'ok'), true);
        http_response_code($ok ? 200 : 503);

        return json_encode([
            'status'    => $ok ? 'ready' : 'not_ready',
            'timestamp' => date('c'),
            'version'   => self::version(),
            'checks'    => $checks,
        ], JSON_PRETTY_PRINT) ?: '{}';
    }

    private static function uptime(): int
    {
        // PHP-FPM/Apache process uptime'ı yerine session başlatılma anı bilgi olarak yeterli.
        return time() - (int) ($_SERVER['REQUEST_TIME'] ?? time());
    }

    private static function version(): string
    {
        $vfile = \app_path('VERSION');
        if (is_file($vfile)) return trim((string) file_get_contents($vfile));
        return 'faz-0.5-dev';
    }

    private static function checkPhpVersion(): array
    {
        $ok = version_compare(PHP_VERSION, '8.2.0', '>=');
        return [
            'ok'      => $ok,
            'value'   => PHP_VERSION,
            'message' => $ok ? null : 'PHP 8.2+ gerekli',
        ];
    }

    private static function checkComposerAutoload(): array
    {
        $path = \app_path('vendor/autoload.php');
        $ok = is_file($path);
        return [
            'ok'      => $ok,
            'value'   => $ok ? 'present' : 'missing',
            'message' => $ok ? null : 'composer install gerekli',
        ];
    }

    private static function checkStorageWritable(): array
    {
        $path = \app_path('storage');
        $ok = is_dir($path) && is_writable($path);
        return [
            'ok'      => $ok,
            'value'   => $ok ? 'writable' : 'not_writable',
            'message' => $ok ? null : 'storage/ yazılabilir olmalı',
        ];
    }

    private static function checkSessionsWritable(): array
    {
        $path = session_save_path() ?: sys_get_temp_dir();
        $ok = is_dir($path) && is_writable($path);
        return [
            'ok'      => $ok,
            'value'   => $path,
            'message' => $ok ? null : 'session_save_path yazılabilir olmalı',
        ];
    }

    private static function checkConfigPresent(): array
    {
        $path = \app_path('config/auth.php');
        $ok = is_file($path);
        return [
            'ok'      => $ok,
            'value'   => $ok ? 'present' : 'missing',
            'message' => $ok ? null : 'config/auth.php mevcut olmalı',
        ];
    }

    private static function checkInflationData(): array
    {
        $path = \app_path('storage/inflation/data.json');
        if (!is_file($path)) {
            // İlk çağrıda repository auto-create eder; warning değil
            return ['ok' => true, 'value' => 'will_be_created', 'message' => null];
        }
        $contents = file_get_contents($path);
        $ok = is_string($contents) && json_decode($contents, true) !== null;
        return [
            'ok'      => $ok,
            'value'   => $ok ? 'valid_json' : 'corrupt',
            'message' => $ok ? null : 'data.json bozuk',
        ];
    }
}

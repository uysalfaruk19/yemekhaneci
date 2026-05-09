<?php

declare(strict_types=1);

namespace Tests\Feature;

use RuntimeException;

/**
 * Feature testleri için arka plan PHP built-in server'ını yönetir.
 * PHPUnit bootstrap.php tarafından bir kez başlatılır, register_shutdown_function ile kapatılır.
 */
final class TestServer
{
    public static int $port = 8888;
    private static ?int $pid = null;

    public static function url(string $path = ''): string
    {
        return 'http://127.0.0.1:' . self::$port . $path;
    }

    public static function start(): void
    {
        if (self::$pid !== null) return;

        $publicDir = dirname(__DIR__, 2) . '/public';
        $cmd = sprintf(
            'php -S 127.0.0.1:%d -t %s %s/index.php > /dev/null 2>&1 & echo $!',
            self::$port,
            escapeshellarg($publicDir),
            escapeshellarg($publicDir)
        );
        $pidStr = trim((string) shell_exec($cmd));
        if ($pidStr === '' || !ctype_digit($pidStr)) {
            throw new RuntimeException('Test server başlatılamadı.');
        }
        self::$pid = (int) $pidStr;

        // Server hazır mı? max 3 sn bekle
        for ($i = 0; $i < 30; $i++) {
            $sock = @fsockopen('127.0.0.1', self::$port, $errno, $errstr, 0.1);
            if ($sock) {
                fclose($sock);
                register_shutdown_function([self::class, 'stop']);
                return;
            }
            usleep(100_000);
        }
        self::stop();
        throw new RuntimeException('Test server cevap vermiyor.');
    }

    public static function stop(): void
    {
        if (self::$pid !== null) {
            @posix_kill(self::$pid, SIGTERM);
            // Eğer hala çalışıyorsa kill -9
            usleep(100_000);
            @posix_kill(self::$pid, SIGKILL);
            self::$pid = null;
        }
    }
}

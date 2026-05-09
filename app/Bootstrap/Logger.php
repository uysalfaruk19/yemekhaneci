<?php

declare(strict_types=1);

namespace App\Bootstrap;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\WebProcessor;
use Throwable;

/**
 * Uygulama logger'ı (Monolog).
 *
 * - storage/logs/app-YYYY-MM-DD.log: 14 günlük rotating file (Monolog level INFO+)
 * - stderr: WARNING+ (production) — Hostinger error logs / Sentry forwarder
 *
 * Faz 1.0a'da Sentry SDK eklenince otomatik forwarding'a açık (sentry/sdk
 * Monolog handler'ı dahil).
 */
final class Logger
{
    private static ?MonologLogger $instance = null;

    public static function get(): MonologLogger
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $logger = new MonologLogger('yemekhaneci');

        $logDir = \app_path('storage/logs');
        if (!is_dir($logDir)) mkdir($logDir, 0755, true);

        // Rotating günlük log dosyası
        $rotating = new RotatingFileHandler(
            $logDir . '/app.log',
            maxFiles: 14,
            level: MonologLogger::INFO
        );
        $rotating->setFormatter(new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s',
            allowInlineLineBreaks: false,
            ignoreEmptyContextAndExtra: true
        ));
        $logger->pushHandler($rotating);

        // stderr (Hostinger error_log + future Sentry handler bağlanır)
        $stderr = new StreamHandler('php://stderr', MonologLogger::WARNING);
        $logger->pushHandler($stderr);

        // Web context: URL, IP, method
        $logger->pushProcessor(new WebProcessor());
        // Caller info: dosya, satır, sınıf
        $logger->pushProcessor(new IntrospectionProcessor(MonologLogger::WARNING));

        self::$instance = $logger;
        return $logger;
    }

    /**
     * Global error/exception handler — uncaught hataları yakalar, loglar, 500 sayfası gösterir.
     */
    public static function registerHandlers(bool $showDebug = false): void
    {
        set_exception_handler(static function (Throwable $e) use ($showDebug): void {
            self::get()->error('Uncaught exception: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
            ]);

            if (PHP_SAPI === 'cli') {
                fwrite(STDERR, sprintf("[%s] %s in %s:%d\n", get_class($e), $e->getMessage(), $e->getFile(), $e->getLine()));
                exit(1);
            }

            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: text/html; charset=utf-8');
            }

            try {
                echo \view('errors.500', [
                    'show_debug' => $showDebug,
                    'exception'  => $e,
                ]);
            } catch (Throwable) {
                echo '<!doctype html><meta charset="utf-8"><title>500</title>'
                   . '<h1>500 — Sunucu hatası</h1>'
                   . '<p>Beklenmeyen bir hata oluştu. Lütfen daha sonra tekrar deneyin.</p>';
            }
            exit(1);
        });

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) return false;
            // ErrorException olarak fırlat — exception handler yakalar
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Bootstrap;

/**
 * Güvenlik HTTP header'ları (PRD §1.4 + OWASP).
 *
 * Apache .htaccess da temel header'ları set ediyor (X-Frame-Options vb.); bu
 * sınıf PHP tarafında nonce-tabanlı CSP ve HSTS gibi dinamik header'ları ekler.
 *
 * Production'da `APP_FORCE_HTTPS=true` ile HTTPS değilse 301 redirect.
 */
final class SecurityHeaders
{
    private static ?string $cspNonce = null;

    public static function send(): void
    {
        if (headers_sent()) return;

        // HTTPS zorunluluğu (production)
        if (self::shouldForceHttps() && !self::isHttps()) {
            $url = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'yemekhaneci.com.tr') . ($_SERVER['REQUEST_URI'] ?? '/');
            header('Location: ' . $url, true, 301);
            exit;
        }

        // HSTS — sadece HTTPS varken yolla (yoksa anlamsız)
        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        // Temel güvenlik header'ları (htaccess'te de var ama burada da garanti)
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(self), camera=(), microphone=(), payment=(self)');
        header('Cross-Origin-Opener-Policy: same-origin');
        header('X-Permitted-Cross-Domain-Policies: none');

        // Content-Security-Policy
        // CDN'den Bootstrap/Alpine/Chart.js çekiyoruz; inline script Alpine component'ler için gerekli.
        // Faz 1.0a'da Blade nonce'lara taşınınca 'unsafe-inline' kaldırılır.
        $nonce = self::cspNonce();
        $cdn = 'https://cdn.jsdelivr.net';
        $fonts = 'https://fonts.googleapis.com https://fonts.gstatic.com';

        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' {$cdn}",          // Alpine inline x-init vb.
            "style-src 'self' 'unsafe-inline' {$cdn} {$fonts}",
            "font-src 'self' data: {$fonts}",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "upgrade-insecure-requests",
        ];

        // Demo'da Report-Only ile başlat — refüze etmez ama violation raporlar
        $cspMode = (getenv('CSP_MODE') ?: 'report-only') === 'enforce'
            ? 'Content-Security-Policy'
            : 'Content-Security-Policy-Report-Only';
        header($cspMode . ': ' . implode('; ', $csp));
    }

    public static function cspNonce(): string
    {
        if (self::$cspNonce === null) {
            self::$cspNonce = bin2hex(random_bytes(16));
        }
        return self::$cspNonce;
    }

    public static function isHttps(): bool
    {
        if (($_SERVER['HTTPS'] ?? '') === 'on') return true;
        if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') return true;
        if ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) return true;
        return false;
    }

    private static function shouldForceHttps(): bool
    {
        return (getenv('APP_FORCE_HTTPS') ?: 'false') === 'true';
    }
}

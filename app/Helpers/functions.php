<?php

declare(strict_types=1);

/**
 * Global yardımcı fonksiyonlar — view render, çıkış kaçışı, CSRF, redirect.
 * Faz 1'de Laravel'in helper'larıyla değiştirilecek.
 */

if (!function_exists('app_path')) {
    function app_path(string $sub = ''): string
    {
        $base = dirname(__DIR__, 2);
        return $sub === '' ? $base : $base . '/' . ltrim($sub, '/');
    }
}

if (!function_exists('config')) {
    /**
     * Dosya bazlı config okuma. config('auth.demo_users.OFU.role')
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $cache = [];
        $segments = explode('.', $key);
        $file = array_shift($segments);

        if (!isset($cache[$file])) {
            $path = app_path('config/' . $file . '.php');
            if (!is_file($path)) {
                return $default;
            }
            $cache[$file] = require $path;
        }

        $value = $cache[$file];
        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('e')) {
    /**
     * HTML kaçış (XSS savunması). Tüm değişken çıktılarda kullan.
     */
    function e(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('view')) {
    /**
     * Basit view render — `resources/views/{name}.php` dosyasını alır,
     * verileri lokal değişkene çevirir ve dize döndürür.
     */
    function view(string $name, array $data = []): string
    {
        $path = app_path('resources/views/' . str_replace('.', '/', $name) . '.php');
        if (!is_file($path)) {
            throw new RuntimeException("View bulunamadı: {$name}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $path;
        return (string) ob_get_clean();
    }
}

if (!function_exists('layout')) {
    /**
     * Layout sarmalayıcı — view içeriğini ana şablon içine yerleştirir.
     */
    function layout(string $layoutName, string $content, array $data = []): string
    {
        return view('layouts.' . $layoutName, array_merge($data, ['__content' => $content]));
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path, int $status = 302): never
    {
        header('Location: ' . $path, true, $status);
        exit;
    }
}

if (!function_exists('back')) {
    function back(): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session başlatılmamış.');
        }
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_check')) {
    function csrf_check(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        $expected = $_SESSION['_csrf_token'] ?? '';
        return $expected !== '' && hash_equals($expected, $token);
    }
}

if (!function_exists('flash')) {
    /**
     * Tek seferlik mesaj kuyrugu — bir sonraki istekte okunur ve silinir.
     */
    function flash(string $key, ?string $value = null): ?string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return null;
        }
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        if (isset($_SESSION['_flash'][$key])) {
            unset($_SESSION['_flash'][$key]);
        }
        return $val;
    }
}

if (!function_exists('old')) {
    /**
     * Form post sonrası eski input değerlerini geri verir.
     */
    function old(string $key, string $default = ''): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $default;
        }
        $val = $_SESSION['_old'][$key] ?? $default;
        return is_string($val) ? $val : $default;
    }
}

if (!function_exists('flash_old')) {
    /** Tüm POST verisini bir sonraki request için flash'la (form yeniden çiziminde kullanılır). */
    function flash_old(array $input): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $sanitized = [];
        foreach ($input as $k => $v) {
            if ($k === 'password' || $k === '_csrf') continue;
            if (is_scalar($v)) {
                $sanitized[$k] = (string) $v;
            }
        }
        $_SESSION['_old'] = $sanitized;
    }
}

if (!function_exists('current_url')) {
    function current_url(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }
}

if (!function_exists('format_money')) {
    function format_money(float|int|string $amount): string
    {
        return number_format((float) $amount, 2, ',', '.') . ' ₺';
    }
}

if (!function_exists('format_pct')) {
    function format_pct(float $value, int $decimals = 2): string
    {
        $sign = $value > 0 ? '+' : '';
        return $sign . number_format($value, $decimals, ',', '.') . '%';
    }
}

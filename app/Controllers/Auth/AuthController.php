<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Auth\SimpleAuth;
use App\Services\AuditLogger;

/**
 * Giriş / çıkış akışı (Faz 0.5 demo — ADR-014).
 * Faz 1.0a'da Laravel Fortify ile değiştirilecek.
 */
final class AuthController
{
    public function showLogin(): string
    {
        $content = \view('auth.login', [
            'error'       => \flash('error'),
            'success'     => \flash('success'),
            'old_username'=> \old('username'),
        ]);

        return \layout('auth', $content, [
            'title' => 'Giriş Yap — Yemekhaneci.com.tr',
        ]);
    }

    public function login(): void
    {
        $token = (string) ($_POST['_csrf'] ?? '');
        if (!\csrf_check($token)) {
            \flash('error', 'Oturum doğrulaması başarısız oldu, lütfen tekrar deneyin.');
            \redirect('/giris-yap');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            \flash('error', 'Kullanıcı adı ve şifre zorunludur.');
            \flash_old($_POST);
            \redirect('/giris-yap');
        }

        // Basit rate limit (session başına dakikada 5 deneme).
        if (!self::rateLimitAllow()) {
            \flash('error', 'Çok fazla başarısız deneme. Lütfen 1 dakika sonra tekrar deneyin.');
            \redirect('/giris-yap');
        }

        if (!SimpleAuth::attempt($username, $password)) {
            self::rateLimitHit();
            AuditLogger::log('auth.login_failed', ['username' => $username], 'anonymous');
            \flash('error', 'Kullanıcı adı veya şifre hatalı.');
            \flash_old($_POST);
            \redirect('/giris-yap');
        }

        $user = SimpleAuth::user();
        AuditLogger::log('auth.login_success', ['role' => $user['role'] ?? null], $username);
        \flash('success', 'Hoş geldin, ' . $user['display_name'] . '.');
        \redirect($user['panel_route']);
    }

    public function logout(): void
    {
        $token = (string) ($_POST['_csrf'] ?? $_GET['_csrf'] ?? '');
        if (!\csrf_check($token)) {
            \redirect('/');
        }

        $username = SimpleAuth::user()['username'] ?? 'unknown';
        SimpleAuth::logout();
        SimpleAuth::startSession();   // yeni boş session başlat (flash için)
        AuditLogger::log('auth.logout', [], $username);
        \flash('success', 'Güvenli şekilde çıkış yaptınız.');
        \redirect('/giris-yap');
    }

    private static function rateLimitAllow(): bool
    {
        $limit = (int) (\config('auth.rate_limits.login_per_min') ?? 5);
        $now = time();
        $window = $_SESSION['_login_attempts'] ?? ['count' => 0, 'reset' => $now + 60];

        if ($now > $window['reset']) {
            $window = ['count' => 0, 'reset' => $now + 60];
        }
        $_SESSION['_login_attempts'] = $window;

        return $window['count'] < $limit;
    }

    private static function rateLimitHit(): void
    {
        $now = time();
        $window = $_SESSION['_login_attempts'] ?? ['count' => 0, 'reset' => $now + 60];
        if ($now > $window['reset']) {
            $window = ['count' => 0, 'reset' => $now + 60];
        }
        $window['count']++;
        $_SESSION['_login_attempts'] = $window;
    }
}

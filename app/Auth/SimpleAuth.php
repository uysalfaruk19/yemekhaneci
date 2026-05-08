<?php

declare(strict_types=1);

namespace App\Auth;

/**
 * Demo kimlik doğrulama (Faz 0.5 prototip — ADR-014).
 * Faz 1.0a'da Laravel + DB tabanlı User modeline taşınacak.
 *
 * Sorumluluk: session başlatma, login attempt, logout, kullanıcı çekme.
 * Kullanıcı listesi config/auth.php'deki demo_users'tan okunur.
 */
final class SimpleAuth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $cfg = config('auth.session', []);
        session_name($cfg['name'] ?? 'yemekhaneci_session');

        session_set_cookie_params([
            'lifetime' => $cfg['lifetime'] ?? 7200,
            'path'     => '/',
            'secure'   => $cfg['secure'] ?? false,
            'httponly' => $cfg['http_only'] ?? true,
            'samesite' => $cfg['same_site'] ?? 'Lax',
        ]);

        session_start();
    }

    public static function attempt(string $username, string $password): bool
    {
        $users = config('auth.demo_users', []);
        $username = trim($username);

        if (!isset($users[$username])) {
            // Timing attack savunması: yanlış kullanıcıda da hash kontrolü yap.
            password_verify($password, '$argon2id$v=19$m=65536,t=4,p=1$ZHVtbXlzYWx0$dummy');
            return false;
        }

        $record = $users[$username];

        if (!password_verify($password, $record['password_hash'])) {
            return false;
        }

        // Session fixation savunması — login sonrası ID yenile.
        session_regenerate_id(true);

        $_SESSION['auth'] = [
            'username'     => $username,
            'role'         => $record['role'],
            'display_name' => $record['display_name'],
            'email'        => $record['email'] ?? null,
            'panel_route'  => $record['panel_route'],
            'logged_in_at' => time(),
        ];

        return true;
    }

    public static function logout(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            self::startSession();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 42000,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'],
                ]
            );
        }

        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['auth']) && is_array($_SESSION['auth']);
    }

    public static function user(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['auth']['role'] ?? null;
    }

    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }
}

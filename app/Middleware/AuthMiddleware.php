<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Auth\SimpleAuth;

/**
 * Erişim kontrolü middleware'leri. Faz 1.0a'da Laravel guard/policy yapısına taşınır.
 *
 * Kullanım:
 *   $router->get('/yonetim', $handler, [AuthMiddleware::requireRole('admin')]);
 */
final class AuthMiddleware
{
    /**
     * Sadece giriş yapmış kullanıcı geçer; aksi halde /giris-yap'a yönlendirilir.
     */
    public static function requireAuth(): callable
    {
        return static function (): bool {
            if (!SimpleAuth::check()) {
                \flash('error', 'Bu sayfaya erişmek için giriş yapmanız gerekiyor.');
                \redirect('/giris-yap');
            }
            return true;
        };
    }

    /**
     * Belirli bir rol gerektirir. Yetkisi yoksa 403.
     */
    public static function requireRole(string $role): callable
    {
        return static function () use ($role): bool {
            if (!SimpleAuth::check()) {
                \flash('error', 'Bu sayfaya erişmek için giriş yapmanız gerekiyor.');
                \redirect('/giris-yap');
            }
            if (!SimpleAuth::hasRole($role)) {
                http_response_code(403);
                echo \view('errors.403', ['required_role' => $role]);
                return false;
            }
            return true;
        };
    }

    /**
     * Misafir-only (giriş yapmışsa kendi paneline yönlendir).
     */
    public static function guestOnly(): callable
    {
        return static function (): bool {
            if (SimpleAuth::check()) {
                $user = SimpleAuth::user();
                \redirect($user['panel_route'] ?? '/');
            }
            return true;
        };
    }
}

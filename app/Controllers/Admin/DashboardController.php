<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Auth\SimpleAuth;

/**
 * Admin paneli — dashboard (Faz 0.5 demo iskelet).
 * Faz 1'de 12 modül + Faz 3.5'te teklif pivotu eklenecek.
 */
final class DashboardController
{
    public function index(): string
    {
        $user = SimpleAuth::user();

        $content = \view('admin.dashboard', [
            'user' => $user,
        ]);

        return \layout('app', $content, [
            'title'  => 'Yönetim Paneli — Yemekhaneci',
            'authed' => true,
            'user'   => $user,
        ]);
    }
}

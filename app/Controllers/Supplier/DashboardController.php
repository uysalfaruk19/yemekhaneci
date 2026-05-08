<?php

declare(strict_types=1);

namespace App\Controllers\Supplier;

use App\Auth\SimpleAuth;

/**
 * Yemekçi paneli — dashboard (Faz 0.5 demo iskelet).
 * Faz 2'de 6 sekmeli maliyet matrisi + gerçek metrikler bağlanacak.
 */
final class DashboardController
{
    public function index(): string
    {
        $user = SimpleAuth::user();

        $content = \view('supplier.dashboard', [
            'user' => $user,
        ]);

        return \layout('app', $content, [
            'title'  => 'Yemekçi Paneli — Yemekhaneci',
            'authed' => true,
            'user'   => $user,
        ]);
    }
}

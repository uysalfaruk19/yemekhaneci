<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Auth\SimpleAuth;

/**
 * Anasayfa (Faz 0.5 demo iskelet).
 * Faz 3'te 9 soru wizard + hızlı teklif modu eklenecek.
 */
final class HomeController
{
    public function index(): string
    {
        $content = \view('public.home', [
            'authed' => SimpleAuth::check(),
            'user'   => SimpleAuth::user(),
        ]);

        return \layout('app', $content, [
            'title'       => 'Yemekhaneci.com.tr — Türkiye\'nin Tarafsız Catering Pazaryeri',
            'description' => 'Kurumsal ve bireysel müşterileri yemekçi firmalarla şeffaf, hızlı ve kontrollü olarak buluşturur.',
            'authed'      => SimpleAuth::check(),
            'user'        => SimpleAuth::user(),
        ]);
    }
}

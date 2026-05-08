<?php

declare(strict_types=1);

namespace App\Controllers\Supplier;

use App\Auth\SimpleAuth;
use App\Services\InflationCalculator;

/**
 * Yemekçi paneli enflasyon hesaplayıcı sayfası — PRD Bölüm 25.6.2.
 * Public sayfayla aynı motoru paylaşır (resources/views/partials/inflation-form.php).
 */
final class InflationController
{
    public function show(): string
    {
        $user = SimpleAuth::user();
        $content = \view('supplier.inflation', [
            'sources' => InflationCalculator::sources(),
            'user'    => $user,
        ]);

        return \layout('app', $content, [
            'title'  => 'Enflasyon Hesaplayıcı — Yemekçi Paneli',
            'authed' => true,
            'user'   => $user,
        ]);
    }
}

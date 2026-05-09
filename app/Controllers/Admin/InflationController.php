<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Auth\SimpleAuth;
use App\Services\InflationCalculator;

/**
 * Admin paneli enflasyon hesaplayıcı + kaynak yönetimi — PRD Bölüm 25.6.3.
 */
final class InflationController
{
    public function show(): string
    {
        $user = SimpleAuth::user();
        $content = \view('admin.inflation', [
            'sources' => InflationCalculator::sources(),
            'user'    => $user,
        ]);

        return \layout('app', $content, [
            'title'  => 'Enflasyon Hesaplayıcı — Yönetim',
            'authed' => true,
            'user'   => $user,
        ]);
    }
}

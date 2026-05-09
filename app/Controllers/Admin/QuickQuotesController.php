<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Auth\SimpleAuth;
use App\Repositories\QuickQuoteRepository;

/**
 * Admin: hızlı teklif (60 sn) talepleri listesi (Faz 3 öne çekme).
 */
final class QuickQuotesController
{
    public function index(): string
    {
        $repo = new QuickQuoteRepository();

        $content = \view('admin.quick-quotes', [
            'quotes'        => $repo->recent(200),
            'total_count'   => $repo->totalCount(),
            'last7_count'   => $repo->last7DaysCount(),
        ]);

        return \layout('app', $content, [
            'title'  => 'Hızlı Teklif Talepleri — Yönetim',
            'authed' => true,
            'user'   => SimpleAuth::user(),
        ]);
    }
}

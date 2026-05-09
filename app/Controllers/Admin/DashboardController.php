<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Auth\SimpleAuth;
use App\Repositories\InflationCalculationRepository;
use App\Repositories\QuickQuoteRepository;
use App\Repositories\SupplierApplicationRepository;

/**
 * Admin paneli — dashboard.
 */
final class DashboardController
{
    public function index(): string
    {
        $user = SimpleAuth::user();

        $supplierRepo = new SupplierApplicationRepository();
        $quoteRepo    = new QuickQuoteRepository();
        $leadRepo     = new InflationCalculationRepository();

        $content = \view('admin.dashboard', [
            'user'           => $user,
            'pending_count'  => $supplierRepo->pendingCount(),
            'active_count'   => $supplierRepo->activeCount(),
            'quote_count_7d' => $quoteRepo->last7DaysCount(),
            'lead_count'     => $leadRepo->leadCount(),
        ]);

        return \layout('app', $content, [
            'title'  => 'Yönetim Paneli — Yemekhaneci',
            'authed' => true,
            'user'   => $user,
        ]);
    }
}

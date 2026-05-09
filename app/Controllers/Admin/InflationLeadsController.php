<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Auth\SimpleAuth;
use App\Bootstrap\Container;
use App\Repositories\Contracts\InflationCalculationRepositoryInterface;

/**
 * Admin: enflasyon hesap kayıtları + KVKK onaylı lead listesi (Faz 0.5.13).
 */
final class InflationLeadsController
{
    public function index(): string
    {
        /** @var InflationCalculationRepositoryInterface $repo */
        $repo = Container::get(InflationCalculationRepositoryInterface::class);

        $content = \view('admin.inflation-leads', [
            'leads'        => $repo->leads(200),
            'totalCount'   => $repo->totalCount(),
            'leadCount'    => $repo->leadCount(),
            'panelCounts'  => $repo->countByPanel(),
        ]);

        return \layout('app', $content, [
            'title'  => 'Enflasyon Lead Yönetimi — Yönetim',
            'authed' => true,
            'user'   => SimpleAuth::user(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Auth\SimpleAuth;
use App\Services\AuditLogger;

/**
 * Admin: KVKK + ticari denetim izi (audit log) görüntüleme.
 */
final class AuditLogController
{
    public function index(): string
    {
        $logger = new AuditLogger();
        $entries = $logger->tail(300);
        $counts  = $logger->countByEvent();

        $content = \view('admin.audit-log', [
            'entries' => $entries,
            'counts'  => $counts,
            'total'   => $logger->totalCount(),
        ]);

        return \layout('app', $content, [
            'title'  => 'Denetim İzi (Audit Log) — Yönetim',
            'authed' => true,
            'user'   => SimpleAuth::user(),
        ]);
    }
}

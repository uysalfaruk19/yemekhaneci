<?php
/**
 * @var array<int, array<string,mixed>> $entries
 * @var array<string,int> $counts
 * @var int $total
 */
$badgeClass = static function(string $event): string {
    if (str_starts_with($event, 'auth.login_failed')) return 'bg-danger-subtle text-danger-emphasis';
    if (str_starts_with($event, 'auth.')) return 'bg-info-subtle text-info-emphasis';
    if (str_starts_with($event, 'source.deleted') || str_starts_with($event, 'monthly_value.deleted')) return 'bg-warning-subtle text-warning-emphasis';
    if (str_starts_with($event, 'source.') || str_starts_with($event, 'monthly_value.')) return 'bg-success-subtle text-success-emphasis';
    if (str_starts_with($event, 'evds.')) return 'bg-primary-subtle text-primary-emphasis';
    if (str_starts_with($event, 'lead.')) return 'bg-secondary-subtle';
    if (str_starts_with($event, 'quote.')) return 'bg-secondary-subtle';
    return 'bg-light';
};
?>
<section class="py-4 py-lg-5">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/yonetim" class="text-decoration-none">Yönetim</a></li>
        <li class="breadcrumb-item"><span class="text-secondary">Sistem</span></li>
        <li class="breadcrumb-item active" aria-current="page">Denetim İzi</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
      <div>
        <span class="badge badge-soft mb-2"><i class="bi bi-clipboard-data me-1"></i>Audit Log</span>
        <h1 class="h3 mb-1">Denetim İzi</h1>
        <p class="text-secondary small mb-0">KVKK uyum gereği 7 yıl saklanır. Tüm admin işlemleri, login denemeleri ve kullanıcı eylemleri burada izlenir.</p>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-4 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Toplam Kayıt</div>
          <div class="h3 mono mb-0"><?= e((string) $total) ?></div>
        </div>
      </div>
      <?php $eventCount = 0; foreach ($counts as $c) $eventCount = max($eventCount, $c); ?>
      <div class="col-md-8">
        <div class="card card-soft p-3">
          <div class="text-secondary small mb-1">Olay Türü Dağılımı (son 5000)</div>
          <div class="d-flex flex-wrap gap-1">
            <?php foreach ($counts as $event => $cnt): ?>
              <span class="badge <?= e($badgeClass($event)) ?>" style="font-size:.78rem;">
                <?= e($event) ?> · <?= e((string) $cnt) ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="card card-soft">
      <div class="card-header bg-white">
        <h2 class="h6 mb-0"><i class="bi bi-list-ul text-brand me-2"></i>Son <?= e((string) count($entries)) ?> Olay (en yeni önce)</h2>
      </div>
      <?php if (empty($entries)): ?>
        <div class="card-body text-center text-secondary py-5">
          <i class="bi bi-inbox display-3 d-block mb-2 text-accent"></i>
          <strong>Henüz audit log yok.</strong>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle small mb-0">
            <thead class="table-light">
              <tr>
                <th>Zaman</th>
                <th>Olay</th>
                <th>Aktör</th>
                <th>IP</th>
                <th>Detay</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($entries as $row): ?>
                <tr>
                  <td class="mono small"><?= e(substr($row['timestamp'] ?? '', 0, 19)) ?></td>
                  <td>
                    <span class="badge <?= e($badgeClass($row['event'] ?? '')) ?>"><?= e($row['event'] ?? '') ?></span>
                  </td>
                  <td><code class="small"><?= e($row['actor'] ?? '') ?></code></td>
                  <td class="mono small text-secondary"><?= e($row['ip'] ?? '') ?></td>
                  <td class="small text-secondary" style="max-width:480px; word-break:break-all;">
                    <code><?= e(json_encode($row['context'] ?? [], JSON_UNESCAPED_UNICODE)) ?></code>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="alert alert-light border mt-3 small">
      <i class="bi bi-shield-lock text-brand me-1"></i>
      <strong>Saklama:</strong> Audit log <code>storage/logs/audit/YYYY-MM-DD.jsonl</code> dosyalarında günlük tutulur (JSONL format).
      Faz 1.0a'da <code>audit_logs</code> DB tablosuna taşınacak; 7 yıllık retention için cron tabanlı arşivleme eklenecek.
    </div>
  </div>
</section>

<?php
/**
 * @var array<int,array<string,mixed>> $suppliers
 * @var string $filter
 * @var int $total_count
 * @var int $pending_count
 * @var int $active_count
 * @var ?string $flash_success
 * @var ?string $flash_error
 */
$statusBadge = static function (string $status): string {
    return match ($status) {
        'pending'   => '<span class="badge bg-warning text-dark">⏳ Başvuru</span>',
        'approved'  => '<span class="badge bg-info-subtle text-info-emphasis">✓ Onaylı</span>',
        'active'    => '<span class="badge bg-success">🟢 Aktif</span>',
        'suspended' => '<span class="badge bg-secondary">⏸ Askıda</span>',
        'rejected'  => '<span class="badge bg-danger-subtle text-danger-emphasis">✗ Reddedildi</span>',
        'manual'    => '<span class="badge bg-primary-subtle text-primary-emphasis">⭐ Manuel</span>',
        default     => '<span class="badge bg-light text-dark">' . htmlspecialchars($status) . '</span>',
    };
};
?>
<section class="py-4 py-lg-5">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/yonetim" class="text-decoration-none">Yönetim</a></li>
        <li class="breadcrumb-item active" aria-current="page">Yemekçi Yönetimi</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
      <div>
        <span class="badge badge-soft mb-2"><i class="bi bi-shop me-1"></i>Yemekçi Yönetimi</span>
        <h1 class="h3 mb-1">Yemekçi Firmalar</h1>
        <p class="text-secondary small mb-0">Başvuruları onayla, yemekçileri düzenle, fiyat ayarlarını güncelle.</p>
      </div>
      <a href="/yonetim/yemekciler/yeni" class="btn btn-brand">
        <i class="bi bi-plus-lg me-1"></i>Yeni Yemekçi Ekle
      </a>
    </div>

    <?php if (!empty($flash_success)): ?>
      <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= e($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
      <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($flash_error) ?></div>
    <?php endif; ?>

    <!-- KPI -->
    <div class="row g-3 mb-4">
      <div class="col-md-3 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Toplam</div>
          <div class="h3 mono mb-0"><?= e((string) $total_count) ?></div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">⏳ Başvuru</div>
          <div class="h3 mono mb-0 text-warning"><?= e((string) $pending_count) ?></div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">🟢 Aktif Yayında</div>
          <div class="h3 mono mb-0 text-success"><?= e((string) $active_count) ?></div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">📊 Müşteri görünür</div>
          <div class="h3 mono mb-0 text-brand"><?= e((string) $active_count) ?></div>
          <div class="small text-secondary">Hızlı teklif sonuçlarında listelenir</div>
        </div>
      </div>
    </div>

    <!-- Filtre -->
    <div class="card card-soft mb-3 p-2">
      <div class="d-flex flex-wrap gap-1">
        <?php foreach (['all'=>'Tümü', 'pending'=>'Başvurular', 'active'=>'Aktif', 'manual'=>'Manuel', 'suspended'=>'Askıda', 'rejected'=>'Reddedilen'] as $key => $label): ?>
          <a href="?durum=<?= e($key) ?>" class="btn btn-sm <?= $filter === $key ? 'btn-brand' : 'btn-outline-secondary' ?>">
            <?= e($label) ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Tablo -->
    <div class="card card-soft">
      <?php if (empty($suppliers)): ?>
        <div class="card-body text-center text-secondary py-5">
          <i class="bi bi-inbox display-3 d-block mb-2 text-accent"></i>
          <strong>Bu durumda yemekçi yok.</strong>
          <div class="small">Manuel ekleme yapabilir veya farklı filtreyi seçebilirsiniz.</div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle small mb-0">
            <thead class="table-light">
              <tr>
                <th>Firma</th>
                <th>Anonim Kod</th>
                <th>Şehir</th>
                <th>Kapasite</th>
                <th class="text-end">Fiyat (₺/öğün)</th>
                <th>Durum</th>
                <th>İletişim</th>
                <th class="text-end">İşlem</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($suppliers as $row): ?>
                <?php $pricing = $row['pricing'] ?? \App\Repositories\SupplierApplicationRepository::defaultPricing(); ?>
                <tr>
                  <td>
                    <strong><?= e($row['company_name'] ?? '?') ?></strong>
                    <?php if (!empty($row['rating'])): ?>
                      <div class="small text-secondary"><i class="bi bi-star-fill text-warning"></i> <?= e((string) $row['rating']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if (!empty($row['anonymous_code'])): ?>
                      <span class="badge bg-brand text-white"><?= e($row['anonymous_code']) ?></span>
                    <?php else: ?>
                      <span class="text-secondary">—</span>
                    <?php endif; ?>
                  </td>
                  <td><?= e($row['city'] ?? '') ?>
                    <?php if (!empty($row['district'])): ?><div class="small text-secondary"><?= e($row['district']) ?></div><?php endif; ?>
                  </td>
                  <td class="mono"><?= e(number_format((int) ($row['daily_capacity'] ?? 0), 0, ',', '.')) ?></td>
                  <td class="mono text-end small">
                    <div>📦 <?= e((string) ($pricing['ekonomik_per_meal'] ?? 0)) ?></div>
                    <div>⭐ <?= e((string) ($pricing['genel_per_meal']    ?? 0)) ?></div>
                    <div>👑 <?= e((string) ($pricing['premium_per_meal']  ?? 0)) ?></div>
                  </td>
                  <td><?= $statusBadge($row['status'] ?? '') ?></td>
                  <td class="small text-secondary">
                    <?php if (!empty($row['contact_name'])): ?><strong><?= e($row['contact_name']) ?></strong><br><?php endif; ?>
                    <?php if (!empty($row['contact_email'])): ?><a href="mailto:<?= e($row['contact_email']) ?>" class="text-decoration-none small"><?= e($row['contact_email']) ?></a><br><?php endif; ?>
                    <?php if (!empty($row['contact_phone'])): ?><span class="mono small"><?= e($row['contact_phone']) ?></span><?php endif; ?>
                  </td>
                  <td class="text-end" style="white-space:nowrap;">
                    <a href="/yonetim/yemekciler/<?= e((string) $row['id']) ?>/duzenle" class="btn btn-sm btn-outline-brand" title="Düzenle">
                      <i class="bi bi-pencil"></i>
                    </a>

                    <?php if (in_array($row['status'] ?? '', ['pending', 'approved', 'suspended', 'rejected'], true)): ?>
                      <form method="post" action="/yonetim/yemekciler/<?= e((string) $row['id']) ?>/onayla" class="d-inline" onsubmit="return confirm('Bu yemekçiyi aktive etmek istediğinize emin misiniz?');">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-success" title="Aktive et"><i class="bi bi-check2-circle"></i></button>
                      </form>
                    <?php endif; ?>

                    <?php if (in_array($row['status'] ?? '', ['active', 'approved', 'manual'], true)): ?>
                      <form method="post" action="/yonetim/yemekciler/<?= e((string) $row['id']) ?>/askiya-al" class="d-inline" onsubmit="return confirm('Yemekçiyi askıya almak istiyor musunuz? Müşteri tarafında görünmez.');">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-warning" title="Askıya al"><i class="bi bi-pause-circle"></i></button>
                      </form>
                    <?php endif; ?>

                    <?php if (($row['status'] ?? '') === 'pending'): ?>
                      <form method="post" action="/yonetim/yemekciler/<?= e((string) $row['id']) ?>/reddet" class="d-inline" onsubmit="return confirm('Başvuruyu reddetmek istiyor musunuz?');">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-danger" title="Reddet"><i class="bi bi-x-circle"></i></button>
                      </form>
                    <?php endif; ?>

                    <form method="post" action="/yonetim/yemekciler/<?= e((string) $row['id']) ?>/sil" class="d-inline" onsubmit="return confirm('TÜMÜYLE silmek istiyor musunuz? Geri alınamaz.');">
                      <?= csrf_field() ?>
                      <button class="btn btn-sm btn-outline-danger" title="Sil"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="alert alert-light border mt-3 small">
      <i class="bi bi-info-circle text-brand me-1"></i>
      <strong>Müşteri tarafı:</strong> Sadece <span class="badge bg-success">🟢 Aktif</span> ve <span class="badge bg-primary-subtle">⭐ Manuel</span> yemekçiler
      anasayfa hızlı teklif sonuçlarında anonim kodla listelenir. Fiyat hesabı düzenleme sayfasındaki
      pricing değerleri × kişi sayısı × öğün × iş günü ile yapılır.
    </div>
  </div>
</section>

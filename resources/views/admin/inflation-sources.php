<?php
/**
 * @var array<int, array<string,mixed>> $sources
 * @var ?string $flash_success
 * @var ?string $flash_error
 */
?>
<section class="py-4 py-lg-5">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/yonetim" class="text-decoration-none">Yönetim</a></li>
        <li class="breadcrumb-item"><span class="text-secondary">Sistem</span></li>
        <li class="breadcrumb-item active" aria-current="page">Enflasyon Kaynakları</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
      <div>
        <span class="badge badge-soft mb-2"><i class="bi bi-database-gear me-1"></i>Kaynak Yönetimi</span>
        <h1 class="h3 mb-1">Enflasyon Endeks Kaynakları</h1>
        <p class="text-secondary mb-0 small">Resmî kaynaklar (TÜİK / ENAG) salt-okunur. UYSA'ya özel formüller bu sayfadan oluşturulup yönetilir.</p>
      </div>
      <a href="/yonetim/sistem/enflasyon-kaynaklari/yeni" class="btn btn-brand">
        <i class="bi bi-plus-lg me-1"></i>Yeni Özel Kaynak
      </a>
    </div>

    <?php if (!empty($flash_success)): ?>
      <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= e($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
      <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($flash_error) ?></div>
    <?php endif; ?>

    <div class="card card-soft">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Ad</th>
              <th>Kod</th>
              <th>Tip</th>
              <th>Baz / Birim</th>
              <th>Renk</th>
              <th class="text-end">İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sources as $src): ?>
              <?php $isOfficial = (bool) ($src['is_official'] ?? false); ?>
              <tr>
                <td>
                  <strong><?= e($src['name']) ?></strong>
                  <?php if ($isOfficial): ?>
                    <span class="badge bg-success ms-1"><i class="bi bi-shield-check me-1"></i>Resmî</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark ms-1"><i class="bi bi-stars me-1"></i>UYSA Özel</span>
                  <?php endif; ?>
                  <?php if (!empty($src['description'])): ?>
                    <div class="small text-secondary mt-1" style="max-width:480px;"><?= e(mb_strimwidth($src['description'], 0, 140, '…')) ?></div>
                  <?php endif; ?>
                </td>
                <td class="mono small text-secondary"><?= e($src['code']) ?></td>
                <td>
                  <?php if (($src['source_type'] ?? '') === 'tuik_api'): ?>
                    <span class="badge bg-info-subtle text-info-emphasis">TÜİK API (otomatik)</span>
                  <?php elseif (($src['source_type'] ?? '') === 'enag_manual'): ?>
                    <span class="badge bg-warning-subtle text-warning-emphasis">Manuel (aylık)</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle">Özel (admin)</span>
                  <?php endif; ?>
                </td>
                <td class="small">
                  <?= e($src['base_period'] ?? '—') ?>
                  <div class="text-secondary" style="font-size:.78rem;">birim: <?= e($src['unit'] ?? 'index') ?></div>
                </td>
                <td>
                  <span class="d-inline-block rounded" style="width:18px;height:18px;background:<?= e($src['color_hex'] ?? '#999') ?>;border:1px solid #ddd;"></span>
                  <span class="mono small text-secondary ms-1"><?= e($src['color_hex'] ?? '') ?></span>
                </td>
                <td class="text-end" style="white-space:nowrap;">
                  <?php if ($isOfficial): ?>
                    <span class="text-secondary small">Salt-okunur</span>
                  <?php else: ?>
                    <a href="/yonetim/sistem/enflasyon-kaynaklari/<?= e($src['code']) ?>/aylik-veri"
                       class="btn btn-sm btn-outline-brand" title="Aylık veri yönetimi">
                      <i class="bi bi-calendar3"></i>
                    </a>
                    <a href="/yonetim/sistem/enflasyon-kaynaklari/<?= e($src['code']) ?>/duzenle"
                       class="btn btn-sm btn-outline-secondary" title="Düzenle">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <form method="post" action="/yonetim/sistem/enflasyon-kaynaklari/<?= e($src['code']) ?>/sil"
                          class="d-inline" onsubmit="return confirm('Bu kaynağı ve tüm aylık verilerini silmek istediğinize emin misiniz?');">
                      <?= csrf_field() ?>
                      <button class="btn btn-sm btn-outline-danger" title="Sil"><i class="bi bi-trash"></i></button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="row g-3 mt-4">
      <div class="col-md-6">
        <div class="card card-soft h-100">
          <div class="card-body">
            <h3 class="h6"><i class="bi bi-cloud-arrow-down text-brand me-2"></i>EVDS Otomatik Çekim</h3>
            <p class="small text-secondary mb-2">TÜİK üç endeksi her ayın 5'inde TCMB EVDS API ile çekilir.</p>
            <ul class="small text-secondary mb-2">
              <li>Son çalışma: <span class="text-secondary">— (henüz tetiklenmedi)</span></li>
              <li>API anahtarı: <span class="badge bg-secondary">.env'de tanımlı değil</span></li>
              <li>Manuel tetikleme: <button class="btn btn-sm btn-link p-0 align-baseline" disabled>Faz 0.5.7'de</button></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card card-soft h-100">
          <div class="card-body">
            <h3 class="h6"><i class="bi bi-stars text-accent me-2"></i>UYSA Özel Formülleri</h3>
            <p class="small text-secondary mb-2">Sektörel endeksler oluşturup aylık veri girebilirsiniz. Örnekler: <em>UYSA Et Endeksi</em>, <em>UYSA Sebze Endeksi</em>, <em>UYSA Süt Endeksi</em>.</p>
            <a href="/yonetim/sistem/enflasyon-kaynaklari/yeni" class="btn btn-sm btn-brand">
              <i class="bi bi-plus-lg me-1"></i>Yeni Kaynak Oluştur
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

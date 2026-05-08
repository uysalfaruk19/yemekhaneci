<?php
/** @var array<int, array{code:string,name:string,color:string,base_period:string}> $sources */
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
        <p class="text-secondary mb-0 small">Resmî kaynaklar (TÜİK / ENAG) ve UYSA'ya özel formüller burada yönetilir.</p>
      </div>
      <button class="btn btn-brand" disabled>
        <i class="bi bi-plus-lg me-1"></i>Yeni Özel Kaynak
        <span class="badge bg-light text-dark ms-2">Faz 0.5.12'de aktif</span>
      </button>
    </div>

    <div class="card card-soft">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Ad</th>
              <th>Kod</th>
              <th>Tip</th>
              <th>Baz Dönem</th>
              <th>Renk</th>
              <th class="text-end">İşlem</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sources as $src): ?>
              <tr>
                <td>
                  <strong><?= e($src['name']) ?></strong>
                  <span class="badge bg-success ms-1"><i class="bi bi-shield-check me-1"></i>Resmî</span>
                </td>
                <td class="mono small text-secondary"><?= e($src['code']) ?></td>
                <td>
                  <?php if (str_starts_with($src['code'], 'tuik_')): ?>
                    <span class="badge bg-info-subtle text-info-emphasis">TÜİK API (otomatik)</span>
                  <?php elseif ($src['code'] === 'enag_tufe'): ?>
                    <span class="badge bg-warning-subtle text-warning-emphasis">Manuel (aylık)</span>
                  <?php else: ?>
                    <span class="badge bg-secondary-subtle">Özel (admin)</span>
                  <?php endif; ?>
                </td>
                <td class="small"><?= e($src['base_period']) ?></td>
                <td>
                  <span class="d-inline-block rounded" style="width:18px;height:18px;background:<?= e($src['color']) ?>;border:1px solid #ddd;"></span>
                  <span class="mono small text-secondary ms-1"><?= e($src['color']) ?></span>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-secondary" disabled title="Faz 0.5.12'de aktif olacak">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-secondary" disabled title="Aylık veri yönetimi (Faz 0.5.12)">
                    <i class="bi bi-calendar3"></i>
                  </button>
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
            <p class="small text-secondary mb-2">Sektörel endeksler (UYSA Et Endeksi, UYSA Sebze Endeksi vb.) admin tarafından tanımlanır ve aylık veri girilir.</p>
            <ul class="small text-secondary mb-0">
              <li>CRUD UI: Faz 0.5.12</li>
              <li>Aylık veri girişi: Faz 0.5.12</li>
              <li>CSV toplu içe aktarım: Faz 0.5.12</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

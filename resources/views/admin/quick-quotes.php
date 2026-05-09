<?php
/**
 * @var array<int, array<string,mixed>> $quotes
 * @var int $total_count
 * @var int $last7_count
 */
$mealLabels = ['ogle' => 'Öğle', 'aksam' => 'Akşam', 'kumanya' => 'Kumanya', 'cocktail' => 'Cocktail'];
?>
<section class="py-4 py-lg-5">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/yonetim" class="text-decoration-none">Yönetim</a></li>
        <li class="breadcrumb-item active" aria-current="page">Hızlı Teklif Talepleri</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
      <div>
        <span class="badge badge-soft mb-2"><i class="bi bi-lightning-charge-fill me-1"></i>Hızlı Teklif Akışı</span>
        <h1 class="h3 mb-1">60 saniye Hızlı Teklif Talepleri</h1>
        <p class="text-secondary small mb-0">Anasayfadan gelen anonim hızlı teklif talepleri burada listelenir.</p>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Toplam Talep</div>
          <div class="h3 mono mb-0"><?= e((string) $total_count) ?></div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Son 7 Gün</div>
          <div class="h3 mono mb-0 text-brand"><?= e((string) $last7_count) ?></div>
        </div>
      </div>
    </div>

    <div class="card card-soft">
      <div class="card-header bg-white">
        <h2 class="h6 mb-0"><i class="bi bi-table text-brand me-2"></i>Talepler (en yeni önce)</h2>
      </div>
      <?php if (empty($quotes)): ?>
        <div class="card-body text-center text-secondary py-5">
          <i class="bi bi-inbox display-3 d-block mb-2 text-accent"></i>
          <strong>Henüz talep yok.</strong>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle small mb-0">
            <thead class="table-light">
              <tr>
                <th>Tarih</th>
                <th>Referans</th>
                <th>Kişi</th>
                <th>Öğün</th>
                <th>Etkinlik</th>
                <th>Şehir/İlçe</th>
                <th>İletişim</th>
                <th>Not</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($quotes as $row): ?>
                <tr>
                  <td class="mono"><?= e($row['created_at']) ?></td>
                  <td><code class="text-brand"><?= e($row['reference']) ?></code></td>
                  <td class="mono text-end"><?= e((string) $row['guest_count']) ?></td>
                  <td><span class="badge bg-secondary-subtle"><?= e($mealLabels[$row['meal_type']] ?? $row['meal_type']) ?></span></td>
                  <td class="mono"><?= e($row['event_date']) ?></td>
                  <td>
                    <?= e($row['city']) ?><?php if (!empty($row['district'])): ?> <span class="text-secondary"> · <?= e($row['district']) ?></span><?php endif; ?>
                  </td>
                  <td class="small">
                    <?php if (!empty($row['contact_name'])): ?><strong><?= e($row['contact_name']) ?></strong><br><?php endif; ?>
                    <?php if (!empty($row['contact_email'])): ?><a href="mailto:<?= e($row['contact_email']) ?>" class="text-decoration-none"><?= e($row['contact_email']) ?></a><br><?php endif; ?>
                    <?php if (!empty($row['contact_phone'])): ?><a href="tel:<?= e($row['contact_phone']) ?>" class="text-decoration-none mono"><?= e($row['contact_phone']) ?></a><?php endif; ?>
                  </td>
                  <td class="small text-secondary" style="max-width:240px;"><?= e($row['notes'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="alert alert-light border mt-3 small">
      <i class="bi bi-info-circle text-brand me-1"></i>
      <strong>Faz 3 öne çekme:</strong> Bu akış MVP'dir; talep alınır ama otomatik yemekçi eşleştirme yapılmaz.
      Faz 3'te gerçek "anonim 3 yemekçi listesi + ortalama fiyat motoru" devreye girecek.
    </div>
  </div>
</section>

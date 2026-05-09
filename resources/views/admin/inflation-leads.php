<?php
/**
 * @var array<int, array<string,mixed>> $leads
 * @var int $totalCount
 * @var int $leadCount
 * @var array<string, int> $panelCounts
 */
?>
<section class="py-4 py-lg-5">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/yonetim" class="text-decoration-none">Yönetim</a></li>
        <li class="breadcrumb-item"><span class="text-secondary">Enflasyon</span></li>
        <li class="breadcrumb-item active" aria-current="page">Lead'ler ve hesap kayıtları</li>
      </ol>
    </nav>

    <h1 class="h3 mb-3">Enflasyon Hesap Kayıtları</h1>
    <p class="text-secondary small mb-4">KVKK onaylı e-posta bırakan müşteriler aşağıda. Anonim hesaplamalar (e-postasız) sayım için saklanır, listede gösterilmez.</p>

    <div class="row g-3 mb-4">
      <div class="col-md-3 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Toplam Hesaplama</div>
          <div class="h3 mono mb-0"><?= e((string) $totalCount) ?></div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">KVKK Onaylı Lead</div>
          <div class="h3 mono mb-0 text-brand"><?= e((string) $leadCount) ?></div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Public</div>
          <div class="h4 mono mb-0"><?= e((string) ($panelCounts['public'] ?? 0)) ?></div>
        </div>
      </div>
      <div class="col-md-3 col-6">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Yemekçi / Admin</div>
          <div class="h4 mono mb-0">
            <?= e((string) ($panelCounts['supplier'] ?? 0)) ?> / <?= e((string) ($panelCounts['admin'] ?? 0)) ?>
          </div>
        </div>
      </div>
    </div>

    <div class="card card-soft">
      <div class="card-header bg-white">
        <h2 class="h6 mb-0"><i class="bi bi-envelope-paper text-brand me-2"></i>Lead Listesi (KVKK Onaylı)</h2>
      </div>
      <?php if (empty($leads)): ?>
        <div class="card-body text-center text-secondary py-5">
          <i class="bi bi-inbox display-3 d-block mb-2 text-accent"></i>
          <strong>Henüz lead yok.</strong>
          <p class="small mb-0">Müşteriler hesaplama sonrası e-posta bırakırsa burada listelenecek.</p>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle small mb-0">
            <thead class="table-light">
              <tr>
                <th>Tarih</th>
                <th>E-posta</th>
                <th>Kaynak</th>
                <th>Sorgu</th>
                <th>Sonuç</th>
                <th>%</th>
                <th>Panel</th>
                <th>IP</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($leads as $row): ?>
                <tr>
                  <td class="mono"><?= e($row['created_at'] ?? '') ?></td>
                  <td><a href="mailto:<?= e($row['email']) ?>" class="text-decoration-none"><?= e($row['email']) ?></a></td>
                  <td class="mono small"><?= e($row['source_code']) ?></td>
                  <td class="mono small">
                    <?= e(substr($row['start_date'], 0, 7)) ?> →
                    <?= e(substr($row['end_date'], 0, 7)) ?><br>
                    <span class="text-secondary"><?= e(format_money($row['start_price'])) ?></span>
                  </td>
                  <td class="mono"><?= e(format_money($row['end_price'])) ?></td>
                  <td class="mono"><?= e(format_pct((float) $row['change_pct'])) ?></td>
                  <td>
                    <span class="badge bg-secondary-subtle"><?= e($row['panel_origin']) ?></span>
                  </td>
                  <td class="mono small text-secondary"><?= e($row['ip_address'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <div class="alert alert-light border mt-3 small">
      <i class="bi bi-shield-lock text-brand me-1"></i>
      <strong>KVKK uyum notu:</strong> E-posta bırakan kullanıcılar açıkça onay vermiştir
      (<code>kvkk_accepted_at</code> NOT NULL). Faz 4'te Brevo/Mailtrap entegrasyonuyla
      hesap sonucu otomatik gönderilecek; pazarlama kullanımı için ayrı <code>marketing_consent</code>
      onayı gerekecek.
    </div>
  </div>
</section>

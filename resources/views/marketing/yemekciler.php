<section class="py-5" style="background: linear-gradient(140deg,#FAF6F0 0%,#F2E8D5 60%,#E8D9B8 100%);">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge badge-soft mb-2"><i class="bi bi-shield-check me-1"></i>Onaylı Yemekçi Vitrini</span>
      <h1 class="display-5">Onaylı Yemekçi Firmalar</h1>
      <p class="lead text-secondary col-lg-8 mx-auto">KVKK gereği firma isimleri liste aşamasında anonim. Detaylı bilgiyi e-posta ile alın veya hızlı teklif akışından doğrudan iletişime geçin.</p>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <!-- Filtre çubuğu (placeholder) -->
    <div class="card card-soft p-3 mb-4">
      <div class="row g-2 align-items-center">
        <div class="col-md-4">
          <label class="form-label small mb-1">Şehir</label>
          <select class="form-select form-select-sm" disabled>
            <option>Tümü (filtre Faz 5'te aktif olacak)</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Kapasite</label>
          <select class="form-select form-select-sm" disabled>
            <option>Tümü</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Sertifika</label>
          <select class="form-select form-select-sm" disabled>
            <option>Tümü</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">&nbsp;</label>
          <button class="btn btn-outline-secondary btn-sm w-100" disabled>Uygula</button>
        </div>
      </div>
    </div>

    <!-- Anonim yemekçi listesi (demo data) -->
    <div class="row g-3">
      <?php
        $demo = [
          ['code' => 'A', 'city' => 'İstanbul / Şişli',     'rating' => 4.8, 'years' => 12, 'cap' => '2.000', 'cert' => ['ISO 22000', 'TSE Helal']],
          ['code' => 'B', 'city' => 'İstanbul / Maltepe',   'rating' => 4.6, 'years' => 18, 'cap' => '5.000', 'cert' => ['ISO 22000', 'HACCP']],
          ['code' => 'C', 'city' => 'Ankara / Çankaya',     'rating' => 4.9, 'years' => 25, 'cap' => '8.000', 'cert' => ['ISO 22000', 'TSE Helal', 'Vegan Cert']],
          ['code' => 'D', 'city' => 'İzmir / Bornova',      'rating' => 4.5, 'years' => 8,  'cap' => '1.500', 'cert' => ['HACCP']],
          ['code' => 'E', 'city' => 'Bursa / Nilüfer',      'rating' => 4.7, 'years' => 15, 'cap' => '3.500', 'cert' => ['ISO 22000', 'Helal']],
          ['code' => 'F', 'city' => 'Kocaeli / Gebze',      'rating' => 4.4, 'years' => 22, 'cap' => '6.000', 'cert' => ['ISO 22000']],
        ];
        foreach ($demo as $row):
      ?>
        <div class="col-md-6 col-lg-4">
          <div class="card card-soft h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <span class="badge bg-brand text-white">Yemekçi <?= e($row['code']) ?></span>
                  <small class="text-secondary ms-1"><i class="bi bi-geo-alt"></i> <?= e($row['city']) ?></small>
                </div>
                <div class="text-end">
                  <strong class="text-brand"><i class="bi bi-star-fill text-warning"></i> <?= e((string) $row['rating']) ?></strong>
                  <div class="small text-secondary"><?= e((string) $row['years']) ?> yıl</div>
                </div>
              </div>
              <div class="small text-secondary mb-2">
                <i class="bi bi-people-fill text-accent me-1"></i>Kapasite: <strong><?= e($row['cap']) ?></strong> öğün/gün
              </div>
              <div class="d-flex flex-wrap gap-1 mb-3">
                <?php foreach ($row['cert'] as $c): ?>
                  <span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-patch-check me-1"></i><?= e($c) ?></span>
                <?php endforeach; ?>
              </div>
              <a href="/#hizli-teklif" class="btn btn-outline-brand btn-sm w-100">
                <i class="bi bi-envelope me-1"></i>Bu yemekçiden teklif al
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="alert alert-light border mt-4 small">
      <i class="bi bi-info-circle text-brand me-1"></i>
      <strong>Demo aşaması:</strong> 6 örnek yemekçi gösteriliyor. Lansman sonrası 100+ onaylı yemekçi listelenecek.
      Gerçek isim ve detaylı sertifikalar müşteri e-posta talebi üzerine paylaşılır (KVKK uyum).
    </div>
  </div>
</section>

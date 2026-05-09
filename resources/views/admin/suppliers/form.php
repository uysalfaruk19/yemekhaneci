<?php
/**
 * @var string $mode  'create' | 'edit'
 * @var array<string,mixed> $record
 * @var array<string, array<int,string>> $errors
 * @var array<string,mixed> $pricing
 */
$isCreate = ($mode ?? 'create') === 'create';
$action = $isCreate
    ? '/yonetim/yemekciler'
    : '/yonetim/yemekciler/' . (int) $record['id'] . '/duzenle';
$certs = ['ISO 22000', 'HACCP', 'TSE', 'TSE Helal', 'Helal Belgesi', 'Vegan Cert', 'Gıda Üretim İzni', 'İşletme Kayıt Belgesi'];
$existingCerts = (array) ($record['certifications'] ?? []);
?>
<section class="py-4 py-lg-5">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/yonetim" class="text-decoration-none">Yönetim</a></li>
        <li class="breadcrumb-item"><a href="/yonetim/yemekciler" class="text-decoration-none">Yemekçiler</a></li>
        <li class="breadcrumb-item active" aria-current="page">
          <?= $isCreate ? 'Yeni Yemekçi' : 'Düzenle: ' . e($record['company_name']) ?>
        </li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
      <div>
        <h1 class="h3 mb-1">
          <?= $isCreate
            ? '<i class="bi bi-plus-lg text-brand me-1"></i>Yeni Yemekçi Ekle'
            : '<i class="bi bi-pencil-square text-brand me-1"></i>Yemekçi Düzenle' ?>
        </h1>
        <?php if (!$isCreate): ?>
          <div class="text-secondary small">
            ID: <code><?= e((string) $record['id']) ?></code>
            <?php if (!empty($record['anonymous_code'])): ?> · Anonim kod: <span class="badge bg-brand text-white"><?= e($record['anonymous_code']) ?></span><?php endif; ?>
            <?php if (!empty($record['status'])): ?> · Durum: <code><?= e($record['status']) ?></code><?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
      <a href="/yonetim/yemekciler" class="btn btn-light border"><i class="bi bi-arrow-left me-1"></i>Listeye dön</a>
    </div>

    <form method="post" action="<?= e($action) ?>" novalidate>
      <?= csrf_field() ?>

      <div class="row g-3">
        <!-- ─── SOL: Firma profili ─── -->
        <div class="col-lg-7">
          <div class="card card-soft mb-3">
            <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-building text-brand me-2"></i>Firma Profili</h2></div>
            <div class="card-body">
              <div class="row g-2">
                <div class="col-md-8">
                  <label class="form-label small">Firma unvanı <span class="text-brand">*</span></label>
                  <input type="text" class="form-control <?= isset($errors['company_name']) ? 'is-invalid' : '' ?>"
                         name="company_name" value="<?= e(old('company_name', $record['company_name'] ?? '')) ?>" required maxlength="200">
                  <?php if (isset($errors['company_name'])): ?>
                    <div class="invalid-feedback"><?= e($errors['company_name'][0]) ?></div>
                  <?php endif; ?>
                </div>
                <div class="col-md-4">
                  <label class="form-label small">Vergi no / TC</label>
                  <input type="text" class="form-control mono <?= isset($errors['tax_number']) ? 'is-invalid' : '' ?>"
                         name="tax_number" value="<?= e(old('tax_number', $record['tax_number'] ?? '')) ?>" pattern="\d{10,11}" maxlength="11">
                </div>

                <div class="col-md-6">
                  <label class="form-label small">Yetkili adı</label>
                  <input type="text" class="form-control" name="contact_name"
                         value="<?= e(old('contact_name', $record['contact_name'] ?? '')) ?>" maxlength="120">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Web</label>
                  <input type="url" class="form-control" name="website"
                         value="<?= e(old('website', $record['website'] ?? '')) ?>" maxlength="200">
                </div>

                <div class="col-md-6">
                  <label class="form-label small">E-posta</label>
                  <input type="email" class="form-control" name="contact_email"
                         value="<?= e(old('contact_email', $record['contact_email'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Telefon</label>
                  <input type="tel" class="form-control" name="contact_phone"
                         value="<?= e(old('contact_phone', $record['contact_phone'] ?? '')) ?>">
                </div>
              </div>
            </div>
          </div>

          <div class="card card-soft mb-3">
            <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-geo-alt text-brand me-2"></i>Lokasyon & Kapasite</h2></div>
            <div class="card-body">
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label small">Şehir <span class="text-brand">*</span></label>
                  <input type="text" class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>"
                         name="city" value="<?= e(old('city', $record['city'] ?? '')) ?>" required maxlength="80">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">İlçe</label>
                  <input type="text" class="form-control" name="district"
                         value="<?= e(old('district', $record['district'] ?? '')) ?>" maxlength="80">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Faaliyet süresi (yıl)</label>
                  <input type="number" class="form-control mono" name="years_in_business"
                         value="<?= e((string) old('years_in_business', (string) ($record['years_in_business'] ?? 0))) ?>" min="0" max="100">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Günlük kapasite (öğün)</label>
                  <input type="number" class="form-control mono" name="daily_capacity"
                         value="<?= e((string) old('daily_capacity', (string) ($record['daily_capacity'] ?? 0))) ?>" min="0" max="1000000">
                </div>
                <div class="col-12">
                  <label class="form-label small">Hizmet bölgeleri (virgülle)</label>
                  <input type="text" class="form-control" name="service_areas"
                         value="<?= e(old('service_areas', implode(', ', (array) ($record['service_areas'] ?? [])))) ?>"
                         placeholder="Şişli, Beşiktaş, Kadıköy">
                </div>
              </div>
            </div>
          </div>

          <div class="card card-soft mb-3">
            <div class="card-header bg-white"><h2 class="h6 mb-0"><i class="bi bi-patch-check text-brand me-2"></i>Sertifikalar & Notlar</h2></div>
            <div class="card-body">
              <div class="row g-2 mb-3">
                <?php foreach ($certs as $cert): ?>
                  <div class="col-md-4 col-6">
                    <div class="form-check">
                      <input type="checkbox" class="form-check-input" id="cert_<?= e(md5($cert)) ?>"
                             name="certifications[]" value="<?= e($cert) ?>"
                             <?= in_array($cert, $existingCerts, true) ? 'checked' : '' ?>>
                      <label class="form-check-label small" for="cert_<?= e(md5($cert)) ?>"><?= e($cert) ?></label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="row g-2">
                <div class="col-md-4">
                  <label class="form-label small">Müşteri puanı (0-5)</label>
                  <input type="number" class="form-control mono" name="rating" step="0.1" min="0" max="5"
                         value="<?= e((string) old('rating', (string) ($record['rating'] ?? 4.5))) ?>">
                </div>
                <div class="col-md-8">
                  <label class="form-label small">Admin notları</label>
                  <textarea class="form-control" name="notes" rows="2" maxlength="2000"><?= e(old('notes', $record['notes'] ?? '')) ?></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ─── SAĞ: Fiyatlandırma ─── -->
        <div class="col-lg-5">
          <div class="card card-soft mb-3" style="position: sticky; top: 80px;">
            <div class="card-header bg-white">
              <h2 class="h6 mb-0"><i class="bi bi-cash-coin text-brand me-2"></i>Fiyatlandırma (₺/öğün)</h2>
            </div>
            <div class="card-body">
              <p class="small text-secondary mb-3">
                Bu yemekçinin müşteri tarafında gözükeceği baz fiyat ve eklentiler. Ortalama hesabı:
                <code>base + addons</code> × kişi × öğün × iş günü.
              </p>

              <strong class="d-block mb-2 small">Segment baz fiyatı</strong>
              <div class="row g-2 mb-3">
                <div class="col-4">
                  <label class="form-label small">📦 Ekonomik</label>
                  <div class="input-group">
                    <input type="number" class="form-control mono" name="pricing[ekonomik_per_meal]" min="0"
                           value="<?= e((string) ($pricing['ekonomik_per_meal'] ?? 110)) ?>">
                    <span class="input-group-text">₺</span>
                  </div>
                </div>
                <div class="col-4">
                  <label class="form-label small">⭐ Genel</label>
                  <div class="input-group">
                    <input type="number" class="form-control mono" name="pricing[genel_per_meal]" min="0"
                           value="<?= e((string) ($pricing['genel_per_meal'] ?? 145)) ?>">
                    <span class="input-group-text">₺</span>
                  </div>
                </div>
                <div class="col-4">
                  <label class="form-label small">👑 Premium</label>
                  <div class="input-group">
                    <input type="number" class="form-control mono" name="pricing[premium_per_meal]" min="0"
                           value="<?= e((string) ($pricing['premium_per_meal'] ?? 195)) ?>">
                    <span class="input-group-text">₺</span>
                  </div>
                </div>
              </div>

              <strong class="d-block mb-2 small">Menü eklentileri</strong>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label small" title="Salata bar 2'den fazla için kişi başı ek">🥗 Ek salata (her +1)</label>
                  <div class="input-group">
                    <input type="number" class="form-control mono" name="pricing[salad_per_extra]" min="0"
                           value="<?= e((string) ($pricing['salad_per_extra'] ?? 4)) ?>">
                    <span class="input-group-text">₺</span>
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small">🍰 Tatlı/meyve dönüşüm</label>
                  <div class="input-group">
                    <input type="number" class="form-control mono" name="pricing[dessert_addon]" min="0"
                           value="<?= e((string) ($pricing['dessert_addon'] ?? 8)) ?>">
                    <span class="input-group-text">₺</span>
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small">🥛 Ayran+yoğurt birlikte</label>
                  <div class="input-group">
                    <input type="number" class="form-control mono" name="pricing[drinks_both_addon]" min="0"
                           value="<?= e((string) ($pricing['drinks_both_addon'] ?? 5)) ?>">
                    <span class="input-group-text">₺</span>
                  </div>
                </div>
              </div>

              <strong class="d-block mb-2 small">Hizmet eklentileri</strong>
              <div class="row g-2 mb-3">
                <div class="col-6">
                  <label class="form-label small">👨‍🍳 Personel (varsa)</label>
                  <div class="input-group">
                    <input type="number" class="form-control mono" name="pricing[personnel_addon]" min="0"
                           value="<?= e((string) ($pricing['personnel_addon'] ?? 25)) ?>">
                    <span class="input-group-text">₺</span>
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small">🛠️ Ekipman (talep var)</label>
                  <div class="input-group">
                    <input type="number" class="form-control mono" name="pricing[equipment_addon]" min="0"
                           value="<?= e((string) ($pricing['equipment_addon'] ?? 18)) ?>">
                    <span class="input-group-text">₺</span>
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small">⏰ Cumartesi (mesaide)</label>
                  <div class="input-group">
                    <input type="number" class="form-control mono" name="pricing[saturday_partial]" min="0"
                           value="<?= e((string) ($pricing['saturday_partial'] ?? 5)) ?>">
                    <span class="input-group-text">₺</span>
                  </div>
                </div>
                <div class="col-6">
                  <label class="form-label small">✅ Cumartesi (tam)</label>
                  <div class="input-group">
                    <input type="number" class="form-control mono" name="pricing[saturday_yes]" min="0"
                           value="<?= e((string) ($pricing['saturday_yes'] ?? 10)) ?>">
                    <span class="input-group-text">₺</span>
                  </div>
                </div>
              </div>

              <label class="form-label small">Fiyat notu (opsiyonel)</label>
              <textarea class="form-control" name="pricing[notes]" rows="2" maxlength="500"
                        placeholder="ör: Bayram dönemi +%15 ek"><?= e($pricing['notes'] ?? '') ?></textarea>

              <hr>

              <button type="submit" class="btn btn-brand w-100">
                <i class="bi bi-check2 me-1"></i><?= $isCreate ? 'Oluştur' : 'Değişiklikleri Kaydet' ?>
              </button>
              <a href="/yonetim/yemekciler" class="btn btn-light border w-100 mt-2">İptal</a>
            </div>
          </div>
        </div>
      </div>
    </form>

    <?php if (!$isCreate): ?>
      <div class="alert alert-light border small mt-3">
        <i class="bi bi-info-circle text-brand me-1"></i>
        <strong>İpucu:</strong> Müşteri "Hızlı teklif" yaptığında, bu yemekçi <strong>aktif</strong> ise
        <strong><?= e($record['anonymous_code'] ?? 'Yemekçi —') ?></strong> kodu ile listelenir.
        Hesap şu formülle yapılır:
        <code>[segment baz fiyatı] + [menü/personel/ekipman/cumartesi addon'ları]</code>
      </div>
    <?php endif; ?>
  </div>
</section>

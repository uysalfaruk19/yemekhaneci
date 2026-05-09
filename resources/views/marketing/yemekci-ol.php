<?php
/**
 * @var ?string $success
 * @var ?string $reference
 * @var ?string $error
 */
?>
<section class="py-5" style="background: linear-gradient(140deg,#FAF6F0 0%,#F2E8D5 60%,#E8D9B8 100%);">
  <div class="container text-center">
    <span class="badge badge-soft mb-2"><i class="bi bi-shop me-1"></i>Yemekçi Başvurusu</span>
    <h1 class="display-5">Catering firmanızı Yemekhaneci'ye taşıyın</h1>
    <p class="lead text-secondary col-lg-8 mx-auto">İlk 6 ay komisyonsuz · Otomatik talep akışı · Maliyet matrisi panelinden anonim ortalama fiyatlandırma</p>
  </div>
</section>

<section class="py-5">
  <div class="container" style="max-width: 880px;">

    <?php if (!empty($success)): ?>
      <div class="card card-soft border-success mb-4">
        <div class="card-body text-center">
          <i class="bi bi-check-circle-fill display-1 text-success"></i>
          <h2 class="display-6 my-3">Başvurunuz alındı!</h2>
          <p>Referans no: <code class="text-brand mono fs-5"><?= e($reference) ?></code></p>
          <p class="text-secondary mb-0"><?= e($success) ?></p>
          <a href="/" class="btn btn-brand mt-3"><i class="bi bi-house me-1"></i>Anasayfaya dön</a>
        </div>
      </div>
    <?php else: ?>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex">
          <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
          <div><?= e($error) ?></div>
        </div>
      <?php endif; ?>

      <div class="card card-soft">
        <div class="card-body p-4">
          <h2 class="h4 mb-3">Başvuru Formu</h2>
          <p class="text-secondary small">KYC + sağlık/hijyen sertifika doğrulama sonrası 7-14 iş günü içinde aktive edilirsiniz.</p>

          <form method="post" action="/yemekci-ol" novalidate>
            <?= csrf_field() ?>

            <fieldset class="border rounded p-3 mb-3">
              <legend class="small text-brand px-2 w-auto"><i class="bi bi-building me-1"></i>Firma Bilgileri</legend>
              <div class="row g-2">
                <div class="col-md-7">
                  <label class="form-label small">Firma unvanı <span class="text-brand">*</span></label>
                  <input type="text" class="form-control" name="company_name" value="<?= e(old('company_name')) ?>" required maxlength="200" placeholder="UYSA Yemek Hizmetleri San. ve Tic. Ltd. Şti.">
                </div>
                <div class="col-md-5">
                  <label class="form-label small">Vergi no / TC <span class="text-brand">*</span></label>
                  <input type="text" class="form-control mono" name="tax_number" value="<?= e(old('tax_number')) ?>" required pattern="\d{10,11}" maxlength="11" inputmode="numeric" placeholder="1234567890">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Faaliyet süresi (yıl)</label>
                  <input type="number" class="form-control mono" name="years_in_business" value="<?= e(old('years_in_business')) ?>" min="0" max="100" placeholder="12">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Web sitesi (varsa)</label>
                  <input type="url" class="form-control" name="website" value="<?= e(old('website')) ?>" placeholder="https://...">
                </div>
              </div>
            </fieldset>

            <fieldset class="border rounded p-3 mb-3">
              <legend class="small text-brand px-2 w-auto"><i class="bi bi-person me-1"></i>İletişim</legend>
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label small">Yetkili kişi <span class="text-brand">*</span></label>
                  <input type="text" class="form-control" name="contact_name" value="<?= e(old('contact_name')) ?>" required maxlength="120" placeholder="Ad Soyad">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Pozisyon</label>
                  <input type="text" class="form-control" name="position" value="<?= e(old('position')) ?>" maxlength="100" placeholder="Genel Müdür">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">E-posta <span class="text-brand">*</span></label>
                  <input type="email" class="form-control" name="contact_email" value="<?= e(old('contact_email')) ?>" required placeholder="info@firma.com.tr">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Telefon <span class="text-brand">*</span></label>
                  <input type="tel" class="form-control" name="contact_phone" value="<?= e(old('contact_phone')) ?>" required placeholder="0532 123 45 67">
                </div>
              </div>
            </fieldset>

            <fieldset class="border rounded p-3 mb-3">
              <legend class="small text-brand px-2 w-auto"><i class="bi bi-geo-alt me-1"></i>Kapasite & Lokasyon</legend>
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label small">Şehir <span class="text-brand">*</span></label>
                  <input type="text" class="form-control" name="city" value="<?= e(old('city')) ?>" required maxlength="80" placeholder="İstanbul">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">İlçe</label>
                  <input type="text" class="form-control" name="district" value="<?= e(old('district')) ?>" maxlength="80" placeholder="Şişli">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Günlük kapasite (öğün) <span class="text-brand">*</span></label>
                  <input type="number" class="form-control mono" name="daily_capacity" value="<?= e(old('daily_capacity')) ?>" required min="50" max="100000" placeholder="2000">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">Hizmet bölgeleri (virgülle)</label>
                  <input type="text" class="form-control" name="service_areas" value="<?= e(old('service_areas')) ?>" placeholder="Şişli, Beşiktaş, Kadıköy">
                </div>
              </div>
            </fieldset>

            <fieldset class="border rounded p-3 mb-3">
              <legend class="small text-brand px-2 w-auto"><i class="bi bi-patch-check me-1"></i>Sertifikalar (varsa)</legend>
              <div class="row g-2">
                <?php
                  $certs = ['ISO 22000', 'HACCP', 'TSE', 'TSE Helal', 'Helal Belgesi', 'Vegan Cert', 'Gıda Üretim İzni', 'İşletme Kayıt Belgesi'];
                  foreach ($certs as $cert):
                ?>
                  <div class="col-md-4 col-6">
                    <div class="form-check">
                      <input type="checkbox" class="form-check-input" id="cert_<?= e(md5($cert)) ?>" name="certifications[]" value="<?= e($cert) ?>">
                      <label class="form-check-label small" for="cert_<?= e(md5($cert)) ?>"><?= e($cert) ?></label>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="form-text small mt-2">Belgeler KYC sürecinde dosya olarak yüklenir.</div>
            </fieldset>

            <div class="mb-3">
              <label class="form-label small">Notlar / özel istekler</label>
              <textarea class="form-control" name="notes" rows="2" maxlength="1000" placeholder="Ek bilgi vermek isterseniz..."><?= e(old('notes')) ?></textarea>
            </div>

            <div class="form-check mb-3">
              <input type="checkbox" class="form-check-input" id="kvkk_supplier" name="kvkk" value="1" required>
              <label class="form-check-label small" for="kvkk_supplier">
                <a href="/yasal/aydinlatma-metni" target="_blank" class="text-brand text-decoration-none">KVKK Aydınlatma Metni</a> ve
                <a href="/yasal/kullanim-kosullari" target="_blank" class="text-brand text-decoration-none">Kullanım Koşulları</a>'nı okudum, kabul ediyorum.
              </label>
            </div>

            <button type="submit" class="btn btn-brand btn-lg w-100">
              <i class="bi bi-send-fill me-1"></i>Başvuruyu Gönder
            </button>
            <div class="text-center small text-secondary mt-3">
              Üyelik ücretsiz · İlk 6 ay komisyon yok · Saatlik 3 başvuru limiti
            </div>
          </form>
        </div>
      </div>

    <?php endif; ?>

  </div>
</section>

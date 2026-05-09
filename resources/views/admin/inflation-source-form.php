<?php
/**
 * Özel kaynak oluşturma / düzenleme formu (Faz 0.5.12).
 *
 * @var string $mode  'create' | 'edit'
 * @var array<string,mixed> $record
 * @var array<string, array<int,string>> $errors
 * @var array<string,string> $units
 */
$isCreate = ($mode ?? 'create') === 'create';
$action = $isCreate
    ? '/yonetim/sistem/enflasyon-kaynaklari'
    : '/yonetim/sistem/enflasyon-kaynaklari/' . $record['code'] . '/duzenle';
?>
<section class="py-4 py-lg-5">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/yonetim" class="text-decoration-none">Yönetim</a></li>
        <li class="breadcrumb-item"><a href="/yonetim/sistem/enflasyon-kaynaklari" class="text-decoration-none">Enflasyon Kaynakları</a></li>
        <li class="breadcrumb-item active" aria-current="page">
          <?= $isCreate ? 'Yeni Özel Kaynak' : 'Düzenle: ' . e($record['name']) ?>
        </li>
      </ol>
    </nav>

    <div class="row">
      <div class="col-lg-8">
        <h1 class="h3 mb-3">
          <?php if ($isCreate): ?>
            <i class="bi bi-plus-lg text-brand me-1"></i>Yeni Özel Enflasyon Kaynağı
          <?php else: ?>
            <i class="bi bi-pencil-square text-brand me-1"></i>Kaynağı Düzenle
          <?php endif; ?>
        </h1>

        <form method="post" action="<?= e($action) ?>" novalidate>
          <?= csrf_field() ?>

          <!-- Kod -->
          <div class="mb-3">
            <label for="code" class="form-label">Kaynak kodu <span class="text-brand">*</span></label>
            <input
              type="text"
              class="form-control mono <?= isset($errors['code']) ? 'is-invalid' : '' ?>"
              id="code"
              name="code"
              value="<?= e(old('code', $record['code'] ?? '')) ?>"
              placeholder="örn: uysa_et_endeksi"
              <?= $isCreate ? '' : 'readonly' ?>
              required>
            <?php if (isset($errors['code'])): ?>
              <div class="invalid-feedback"><?= e($errors['code'][0]) ?></div>
            <?php else: ?>
              <div class="form-text small">
                Küçük harf + rakam + altçizgi. Harfle başlamalı, 3-64 karakter. <code>tuik_</code> ve <code>enag_</code> rezerve.
                <?php if (!$isCreate): ?> Oluşturulduktan sonra değiştirilemez.<?php endif; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Ad -->
          <div class="mb-3">
            <label for="name" class="form-label">Görünür ad <span class="text-brand">*</span></label>
            <input
              type="text"
              class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
              id="name"
              name="name"
              value="<?= e(old('name', $record['name'] ?? '')) ?>"
              placeholder="örn: UYSA Et Endeksi"
              maxlength="150"
              required>
            <?php if (isset($errors['name'])): ?>
              <div class="invalid-feedback"><?= e($errors['name'][0]) ?></div>
            <?php else: ?>
              <div class="form-text small">Müşteri ve admin paneline bu ad ile düşer (3-150 karakter).</div>
            <?php endif; ?>
          </div>

          <!-- Açıklama -->
          <div class="mb-3">
            <label for="description" class="form-label">Açıklama</label>
            <textarea
              class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
              id="description"
              name="description"
              rows="3"
              maxlength="2000"
              placeholder="UYSA'nın saha verisinden hesapladığı kırmızı et endeksi (₺/kg, ortalama tedarikçi alış)..."><?= e(old('description', $record['description'] ?? '')) ?></textarea>
            <?php if (isset($errors['description'])): ?>
              <div class="invalid-feedback"><?= e($errors['description'][0]) ?></div>
            <?php endif; ?>
          </div>

          <div class="row g-3">
            <!-- Birim -->
            <div class="col-md-6 mb-3">
              <label for="unit" class="form-label">Birim</label>
              <select class="form-select" id="unit" name="unit">
                <?php foreach ($units as $key => $label): ?>
                  <option value="<?= e($key) ?>" <?= ($record['unit'] ?? 'index') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text small">Endeks (baz=100) veya doğrudan birim fiyat (₺/kg, ₺/lt vb.).</div>
            </div>

            <!-- Baz dönem -->
            <div class="col-md-6 mb-3">
              <label for="base_period" class="form-label">Baz dönem</label>
              <input
                type="text"
                class="form-control mono"
                id="base_period"
                name="base_period"
                value="<?= e(old('base_period', $record['base_period'] ?? '')) ?>"
                placeholder="örn: 2024=100"
                maxlength="32">
              <div class="form-text small">Endeks için zorunlu, birim fiyat (₺/kg) için isteğe bağlı.</div>
            </div>

            <!-- Renk -->
            <div class="col-md-6 mb-3">
              <label for="color_hex" class="form-label">Grafik rengi</label>
              <div class="input-group">
                <input
                  type="color"
                  class="form-control form-control-color"
                  id="color_hex"
                  name="color_hex"
                  value="<?= e(old('color_hex', $record['color_hex'] ?? '#2A5C6B')) ?>"
                  style="max-width:60px;">
                <input
                  type="text"
                  class="form-control mono <?= isset($errors['color_hex']) ? 'is-invalid' : '' ?>"
                  value="<?= e(old('color_hex', $record['color_hex'] ?? '#2A5C6B')) ?>"
                  oninput="document.getElementById('color_hex').value = this.value;"
                  placeholder="#RRGGBB"
                  maxlength="7">
              </div>
              <?php if (isset($errors['color_hex'])): ?>
                <div class="text-danger small mt-1"><?= e($errors['color_hex'][0]) ?></div>
              <?php endif; ?>
            </div>

            <!-- Sıralama -->
            <div class="col-md-6 mb-3">
              <label for="display_order" class="form-label">Sıralama önceliği</label>
              <input
                type="number"
                min="1"
                max="9999"
                class="form-control mono <?= isset($errors['display_order']) ? 'is-invalid' : '' ?>"
                id="display_order"
                name="display_order"
                value="<?= e((string) old('display_order', (string) ($record['display_order'] ?? 100))) ?>">
              <?php if (isset($errors['display_order'])): ?>
                <div class="invalid-feedback"><?= e($errors['display_order'][0]) ?></div>
              <?php else: ?>
                <div class="form-text small">Düşük sayı önce gösterilir. Resmî kaynaklar 10-40 arası.</div>
              <?php endif; ?>
            </div>
          </div>

          <?php if (!$isCreate): ?>
            <div class="form-check mb-3">
              <input
                type="checkbox"
                class="form-check-input"
                id="is_active"
                name="is_active"
                value="1"
                <?= ($record['is_active'] ?? true) ? 'checked' : '' ?>>
              <label for="is_active" class="form-check-label">Aktif (müşteri ve yemekçi panelinde gösterilir)</label>
            </div>
          <?php endif; ?>

          <hr>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-brand">
              <i class="bi bi-check2 me-1"></i><?= $isCreate ? 'Oluştur ve Aylık Veriye Geç' : 'Değişiklikleri Kaydet' ?>
            </button>
            <a href="/yonetim/sistem/enflasyon-kaynaklari" class="btn btn-light border">İptal</a>
          </div>
        </form>
      </div>

      <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="card card-soft">
          <div class="card-body">
            <h2 class="h6"><i class="bi bi-info-circle text-brand me-2"></i>Özel Kaynaklar Hakkında</h2>
            <ul class="small text-secondary mb-2 ps-3">
              <li>Resmî kaynaklar (TÜİK / ENAG) silinemez veya değiştirilemez.</li>
              <li>Kod oluşturulduktan sonra değiştirilemez (FK referansları için).</li>
              <li>Aylık veri eklemeden hesap yapılamaz; en az 2 ay girmelisiniz (başlangıç + hedef).</li>
              <li>Faz 1.0a'da bu kayıtlar <code>inflation_sources</code> tablosuna otomatik taşınacak.</li>
            </ul>
            <hr>
            <strong class="d-block small mb-1">Kullanım örnekleri</strong>
            <div class="small text-secondary">
              <code>uysa_et_endeksi</code> · UYSA Et Endeksi · ₺/kg<br>
              <code>uysa_sebze</code> · UYSA Sebze Endeksi · ₺/kg<br>
              <code>uysa_sut_urunleri</code> · UYSA Süt Endeksi · ₺/lt
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

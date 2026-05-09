<?php
/**
 * @var bool $enabled
 * @var ?string $enabled_at
 * @var int $recovery_remaining
 */
?>
<section class="py-4 py-lg-5">
  <div class="container" style="max-width:680px;">
    <h1 class="h3 mb-3"><i class="bi bi-shield-check text-success me-2"></i>İki Adımlı Doğrulama Aktif</h1>

    <div class="card card-soft p-4 my-3">
      <p class="mb-2">Hesabınız 2FA ile korunuyor.</p>
      <ul class="small mb-3">
        <li>Aktifleştirildi: <code><?= e($enabled_at ?? '?') ?></code></li>
        <li>Kalan kurtarma kodu: <strong><?= e((string) $recovery_remaining) ?></strong> / 8</li>
      </ul>

      <?php if ($recovery_remaining < 3): ?>
        <div class="alert alert-warning small">
          <i class="bi bi-exclamation-triangle-fill me-1"></i>
          Kurtarma kodlarınız azaldı (<?= e((string) $recovery_remaining) ?> kaldı).
          Yeni kodlar için 2FA'yı kapatıp tekrar aktifleştirebilirsiniz.
        </div>
      <?php endif; ?>

      <hr>

      <details>
        <summary class="text-danger small">
          <i class="bi bi-shield-slash me-1"></i>İki adımlı doğrulamayı kapat
        </summary>
        <form method="post" action="/hesap/iki-adimli-dogrulama/kapat" class="mt-3 row g-2 align-items-end">
          <?= csrf_field() ?>
          <div class="col-sm-7">
            <label class="form-label small">Güncel 6 haneli kod</label>
            <input type="text" class="form-control mono" name="token" pattern="\d{6}" maxlength="6" required>
          </div>
          <div class="col-sm-5">
            <button type="submit" class="btn btn-danger w-100">
              <i class="bi bi-shield-slash me-1"></i>2FA'yı kapat
            </button>
          </div>
          <div class="col-12">
            <small class="text-secondary">
              Kapattıktan sonra tüm kurtarma kodları geçersiz olur.
            </small>
          </div>
        </form>
      </details>
    </div>
  </div>
</section>

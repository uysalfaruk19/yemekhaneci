<?php
/**
 * @var string $secret
 * @var string $uri
 * @var string $qr_url
 * @var ?string $error
 * @var array $user
 */
?>
<section class="py-4 py-lg-5">
  <div class="container" style="max-width:760px;">
    <h1 class="h3 mb-2"><i class="bi bi-shield-lock text-brand me-2"></i>İki Adımlı Doğrulama (2FA) Kurulumu</h1>
    <p class="text-secondary">Hesabınızı yetkisiz girişlere karşı koruyun. PRD §5.1.2 admin için 2FA zorunlu.</p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?></div>
    <?php endif; ?>

    <ol class="card card-soft p-4 my-3">
      <li class="mb-3">
        <strong>Authenticator uygulaması yükleyin</strong> (Google Authenticator, Authy, Microsoft Authenticator, 1Password vb.).
      </li>
      <li class="mb-3">
        <strong>QR kodu tarayın veya secret'ı manuel girin:</strong>
        <div class="row g-3 align-items-center mt-2">
          <div class="col-md-5 text-center">
            <img src="<?= e($qr_url) ?>" alt="2FA QR kodu" class="img-fluid border rounded" style="max-width:220px;">
          </div>
          <div class="col-md-7">
            <label class="form-label small text-secondary mb-1">Manuel giriş için secret:</label>
            <div class="input-group">
              <input type="text" class="form-control mono small" value="<?= e(chunk_split($secret, 4, ' ')) ?>" readonly>
              <button class="btn btn-outline-secondary" type="button"
                      onclick="navigator.clipboard.writeText('<?= e($secret) ?>'); this.innerHTML='<i class=\'bi bi-check2\'></i> Kopyalandı';">
                <i class="bi bi-clipboard"></i>
              </button>
            </div>
            <small class="text-secondary d-block mt-2">
              Algoritma: SHA1 · 6 hane · 30 saniye<br>
              Hesap: <code><?= e($user['email'] ?? $user['username']) ?></code>
            </small>
          </div>
        </div>
      </li>
      <li>
        <strong>Authenticator'ın gösterdiği 6 haneli kodu girin:</strong>
        <form method="post" action="/hesap/iki-adimli-dogrulama/onayla" class="row g-2 align-items-end mt-2">
          <?= csrf_field() ?>
          <div class="col-sm-7">
            <input type="text" class="form-control mono text-center"
                   style="letter-spacing:.4em; font-size:1.3rem;"
                   name="token" pattern="\d{6}" maxlength="6" required autocomplete="one-time-code"
                   placeholder="••••••">
          </div>
          <div class="col-sm-5">
            <button type="submit" class="btn btn-brand w-100">
              <i class="bi bi-check2 me-1"></i>Aktifleştir
            </button>
          </div>
        </form>
      </li>
    </ol>

    <div class="alert alert-light border small">
      <i class="bi bi-info-circle text-brand me-1"></i>
      Aktifleştirdikten sonra <strong>8 adet kurtarma kodu</strong> üretilir.
      Telefonunuzu kaybederseniz bunlarla giriş yapabilirsiniz — güvenli yerde saklayın.
    </div>
  </div>
</section>

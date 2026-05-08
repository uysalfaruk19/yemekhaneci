<?php
/**
 * Giriş formu (Faz 0.5 demo).
 * @var ?string $error
 * @var ?string $success
 * @var string  $old_username
 */
?>
<h1 class="h4 mb-1" style="font-family:'Cormorant Garamond', serif; color:#6B1F2A; font-weight:700;">Giriş Yap</h1>
<p class="text-secondary small mb-3">Yemekçi veya yönetim panelinize erişmek için hesap bilgilerinizi girin.</p>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger d-flex align-items-start" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
  <div class="alert alert-success d-flex align-items-start" role="alert">
    <i class="bi bi-check-circle-fill me-2 mt-1"></i>
    <div><?= e($success) ?></div>
  </div>
<?php endif; ?>

<form method="post" action="/giris-yap" autocomplete="on" novalidate>
  <?= csrf_field() ?>

  <div class="mb-3">
    <label for="username" class="form-label">Kullanıcı adı</label>
    <div class="input-group">
      <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
      <input
        type="text"
        class="form-control"
        id="username"
        name="username"
        value="<?= e($old_username) ?>"
        required
        autofocus
        autocomplete="username"
        placeholder="Kullanıcı adınız"
        aria-describedby="usernameHelp">
    </div>
  </div>

  <div class="mb-3">
    <label for="password" class="form-label d-flex justify-content-between">
      <span>Şifre</span>
    </label>
    <div class="input-group" x-data="{ show: false }">
      <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
      <input
        :type="show ? 'text' : 'password'"
        class="form-control"
        id="password"
        name="password"
        required
        autocomplete="current-password"
        placeholder="••••">
      <button class="btn btn-outline-secondary" type="button" @click="show = !show" :aria-label="show ? 'Şifreyi gizle' : 'Şifreyi göster'">
        <i class="bi" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
      </button>
    </div>
  </div>

  <button type="submit" class="btn btn-brand w-100">
    <i class="bi bi-box-arrow-in-right me-1"></i>Giriş Yap
  </button>
</form>

<hr class="my-4">

<div class="demo-hint">
  <strong class="d-block mb-1"><i class="bi bi-info-circle text-brand me-1"></i>Demo hesaplar (Faz 0.5)</strong>
  <div>Yemekçi: <code>Uysa</code> / <code>1234</code></div>
  <div>Admin: <code>OFU</code> / <code>1234</code></div>
  <div class="text-secondary small mt-2">Bu hesaplar yalnızca demo amaçlıdır. Faz 1'de DB tabanlı kullanıcı sistemi devreye girecek.</div>
</div>

<div class="text-center mt-3">
  <a href="/" class="text-decoration-none small text-secondary"><i class="bi bi-arrow-left me-1"></i>Anasayfaya dön</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>

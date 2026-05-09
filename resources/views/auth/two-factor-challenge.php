<?php
/**
 * @var ?string $username
 * @var int $recovery_remaining
 * @var ?string $error
 */
?>
<h1 class="h4 mb-1" style="font-family:'Cormorant Garamond', serif; color:#6B1F2A; font-weight:700;">
  <i class="bi bi-shield-lock me-1"></i>İki adımlı doğrulama
</h1>
<p class="text-secondary small mb-3">
  <code><?= e($username ?? '') ?></code> hesabı için Authenticator uygulamanızdaki 6 haneli kodu girin.
</p>

<?php if (!empty($error)): ?>
  <div class="alert alert-danger d-flex align-items-start" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
    <div><?= e($error) ?></div>
  </div>
<?php endif; ?>

<form method="post" action="/giris-yap/2fa" autocomplete="off" novalidate x-data="{ recovery: false }">
  <?= csrf_field() ?>
  <input type="hidden" name="use_recovery" :value="recovery ? '1' : ''">

  <div class="mb-3">
    <label for="token" class="form-label">
      <span x-show="!recovery">6 haneli kod</span>
      <span x-show="recovery" x-cloak>Kurtarma kodu (8 karakter)</span>
    </label>
    <input
      type="text"
      class="form-control mono text-center"
      style="letter-spacing: .4em; font-size: 1.4rem;"
      id="token"
      name="token"
      :pattern="recovery ? '[A-Fa-f0-9]{8}' : '\\d{6}'"
      :maxlength="recovery ? 8 : 6"
      required
      autofocus
      autocomplete="one-time-code"
      inputmode="text"
      placeholder="••••••">
  </div>

  <button type="submit" class="btn btn-brand w-100">
    <i class="bi bi-check2 me-1"></i>Doğrula
  </button>
</form>

<hr class="my-4">

<?php if ($recovery_remaining > 0): ?>
  <div x-data="{}" class="text-center small">
    <button type="button" class="btn btn-link p-0 text-secondary text-decoration-none" @click="document.querySelector('[x-data]').__x.$data.recovery = !document.querySelector('[x-data]').__x.$data.recovery">
      <i class="bi bi-key me-1"></i>Telefonum yanımda yok — kurtarma kodu kullan (<?= e((string) $recovery_remaining) ?> kaldı)
    </button>
  </div>
<?php else: ?>
  <div class="alert alert-warning small">
    Kurtarma kodlarınız tükendi. 2FA'yı sıfırlamak için
    <a href="mailto:destek@uysa.com.tr" class="text-decoration-none">destek@uysa.com.tr</a> ile iletişime geçin.
  </div>
<?php endif; ?>

<div class="text-center mt-3">
  <form method="post" action="/cikis-yap" class="d-inline">
    <?= csrf_field() ?>
    <button class="btn btn-link p-0 small text-secondary text-decoration-none" type="submit">
      <i class="bi bi-arrow-left me-1"></i>Vazgeç ve çıkış yap
    </button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>

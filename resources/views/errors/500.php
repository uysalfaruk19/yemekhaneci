<?php
/**
 * @var bool $show_debug
 * @var \Throwable $exception
 */
$showDebug = $show_debug ?? false;
?>
<?= layout('app', '
<section class="py-5 py-lg-6 text-center">
  <div class="container">
    <div class="display-1 text-brand mb-2"><i class="bi bi-exclamation-octagon"></i></div>
    <h1 class="display-5 mb-2">500 — Sunucu Hatası</h1>
    <p class="text-secondary col-lg-7 mx-auto mb-4">
      Beklenmeyen bir hata oluştu ve teknik ekibimize otomatik bildirildi.
      Lütfen birkaç dakika sonra tekrar deneyin veya
      <a href="mailto:destek@uysa.com.tr" class="text-brand text-decoration-none">destek@uysa.com.tr</a>
      adresine yazın.
    </p>
    <a href="/" class="btn btn-brand"><i class="bi bi-house me-1"></i>Anasayfaya dön</a>
    <a href="javascript:history.back()" class="btn btn-outline-brand ms-2">
      <i class="bi bi-arrow-left me-1"></i>Geri dön
    </a>

    ' . ($showDebug ? '
    <details class="text-start mt-5 mx-auto" style="max-width: 800px;">
      <summary class="text-secondary small">Hata detayı (sadece geliştirme modunda görünür)</summary>
      <pre class="mono small bg-cream p-3 mt-2 rounded" style="white-space: pre-wrap; word-break: break-all;">'
        . e(get_class($exception)) . ': ' . e($exception->getMessage())
        . "\n\n" . e($exception->getFile()) . ':' . e((string) $exception->getLine())
        . "\n\n" . e($exception->getTraceAsString())
      . '</pre>
    </details>
    ' : '') . '
  </div>
</section>
', ['title' => '500 — Sunucu Hatası | Yemekhaneci', 'authed' => \App\Auth\SimpleAuth::check(), 'user' => \App\Auth\SimpleAuth::user()]);

<?php
/**
 * @var array<int,string> $codes
 * @var string $username
 */
$plain = implode("\n", $codes);
?>
<section class="py-4 py-lg-5">
  <div class="container" style="max-width:680px;">
    <h1 class="h3 mb-2"><i class="bi bi-shield-check text-success me-2"></i>2FA aktifleştirildi 🎉</h1>
    <div class="alert alert-warning border my-3">
      <strong><i class="bi bi-exclamation-triangle-fill me-1"></i>Bu kodları şimdi kaydedin!</strong><br>
      Aşağıdaki <strong>8 kurtarma kodu</strong> bir daha gösterilmeyecek. Telefonunuzu kaybederseniz bunlarla giriş yapabilirsiniz.
      Her kod sadece <strong>bir kez</strong> kullanılabilir.
    </div>

    <div class="card card-soft p-4">
      <div class="row g-2">
        <?php foreach ($codes as $i => $code): ?>
          <div class="col-sm-6">
            <code class="d-block p-2 bg-cream rounded text-center mono fs-5"><?= e($code) ?></code>
          </div>
        <?php endforeach; ?>
      </div>

      <hr>

      <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-brand"
                onclick="navigator.clipboard.writeText('<?= e($plain) ?>'); this.innerHTML='<i class=\'bi bi-check2\'></i> Panoya kopyalandı';">
          <i class="bi bi-clipboard me-1"></i>Tümünü kopyala
        </button>
        <a href="data:text/plain;charset=utf-8,<?= e(rawurlencode($plain)) ?>"
           download="yemekhaneci-2fa-recovery-<?= e($username) ?>.txt" class="btn btn-outline-brand">
          <i class="bi bi-download me-1"></i>İndir (.txt)
        </a>
        <button type="button" class="btn btn-light border" onclick="window.print()">
          <i class="bi bi-printer me-1"></i>Yazdır
        </button>
      </div>

      <hr>

      <p class="small text-secondary mb-3">
        Kodları güvenli bir şekilde sakladığınızı onayladıktan sonra panele dönebilirsiniz.
      </p>
      <a href="/yonetim" class="btn btn-success">
        <i class="bi bi-check2 me-1"></i>Kodları sakladım, panele dön
      </a>
    </div>
  </div>
</section>

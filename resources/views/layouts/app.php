<?php
/**
 * Ana layout — public ve panel sayfaları için ortak shell.
 * @var string $__content
 * @var string $title
 * @var string|null $description
 * @var bool $authed
 * @var array|null $user
 */
$title       = $title       ?? 'Yemekhaneci.com.tr';
$description = $description ?? 'Türkiye\'nin tarafsız catering pazaryeri.';
$authed      = $authed      ?? false;
$user        = $user        ?? null;
?><!doctype html>
<html lang="tr" data-theme="light">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="description" content="<?= e($description) ?>">
<title><?= e($title) ?></title>

<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext x='50' y='66' font-size='62' text-anchor='middle'%3E🍽️%3C/text%3E%3C/svg%3E">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=Manrope:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

<style>
  :root {
    --brand-primary: #6B1F2A;
    --brand-accent:  #C9A961;
    --brand-cream:   #FAF6F0;
    --brand-ink:     #1F1A17;
    --brand-muted:   #8C7E72;
  }
  html, body { height: 100%; }
  body {
    font-family: 'Manrope', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
    background: var(--brand-cream);
    color: var(--brand-ink);
  }
  h1, h2, h3, .display-1, .display-2, .display-3, .display-4, .display-5, .display-6 {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-weight: 600;
    letter-spacing: -0.01em;
  }
  .num, .mono { font-family: 'JetBrains Mono', ui-monospace, monospace; }
  .btn-brand {
    background: var(--brand-primary);
    border-color: var(--brand-primary);
    color: #fff;
  }
  .btn-brand:hover, .btn-brand:focus { background: #511620; border-color: #511620; color: #fff; }
  .btn-outline-brand {
    color: var(--brand-primary);
    border-color: var(--brand-primary);
  }
  .btn-outline-brand:hover, .btn-outline-brand:focus {
    background: var(--brand-primary);
    border-color: var(--brand-primary);
    color: #fff;
  }
  .text-brand { color: var(--brand-primary) !important; }
  .text-accent { color: var(--brand-accent) !important; }
  .bg-brand { background: var(--brand-primary) !important; }
  .bg-cream { background: var(--brand-cream) !important; }
  .navbar-brand { font-family: 'Cormorant Garamond', serif; font-weight: 700; font-size: 1.5rem; }
  .navbar-brand .dot { color: var(--brand-accent); }
  .card-soft { border: 1px solid #ECE3D7; box-shadow: 0 1px 0 rgba(0,0,0,.02); }
  .badge-soft { background: #f3ead9; color: #6B1F2A; font-weight: 600; }
  footer { background: var(--brand-ink); color: #ECE3D7; }
  .footer a { color: var(--brand-accent); text-decoration: none; }
  .footer a:hover { color: #fff; }
  /* Form */
  .form-control, .form-select { border-color: #DDD3C2; }
  .form-control:focus, .form-select:focus {
    border-color: var(--brand-accent);
    box-shadow: 0 0 0 .15rem rgba(201,169,97,.25);
  }
</style>
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
  <div class="container">
    <a class="navbar-brand text-brand" href="/">Yemekhaneci<span class="dot">.com.tr</span></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menüyü aç/kapat">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/">Ana sayfa</a></li>
        <li class="nav-item"><a class="nav-link" href="/araclar/enflasyon-hesaplayici"><i class="bi bi-graph-up-arrow me-1"></i>Enflasyon Hesaplayıcı</a></li>
      </ul>
      <ul class="navbar-nav">
        <?php if ($authed): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-person-circle me-1"></i><?= e($user['display_name']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?= e($user['panel_route']) ?>"><i class="bi bi-speedometer2 me-2"></i>Panelim</a></li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form method="post" action="/cikis-yap" class="m-0 p-0">
                  <?= csrf_field() ?>
                  <button type="submit" class="dropdown-item">
                    <i class="bi bi-box-arrow-right me-2"></i>Çıkış yap
                  </button>
                </form>
              </li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-brand ms-lg-2" href="/giris-yap">
              <i class="bi bi-box-arrow-in-right me-1"></i>Giriş Yap
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<?php $flashSuccess = flash('success'); $flashError = flash('error'); ?>
<?php if ($flashSuccess || $flashError): ?>
  <div class="container mt-3">
    <?php if ($flashSuccess): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= e($flashSuccess) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
      </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($flashError) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<main class="flex-grow-1">
  <?= $__content ?? '' ?>
</main>

<footer class="footer mt-auto py-4">
  <div class="container">
    <div class="row gy-3 align-items-center">
      <div class="col-md-6">
        <strong class="text-accent">Yemekhaneci.com.tr</strong> — Türkiye'nin Tarafsız Catering Pazaryeri
        <div class="small text-secondary mt-1">© 2026 UYSA Yemek Hizmetleri</div>
      </div>
      <div class="col-md-6 text-md-end">
        <a href="/araclar/enflasyon-hesaplayici" class="me-3">Enflasyon Hesaplayıcı</a>
        <a href="#" class="me-3">KVKK</a>
        <a href="#">İletişim</a>
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous" defer></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js" defer></script>

</body>
</html>

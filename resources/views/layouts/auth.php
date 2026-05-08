<?php
/**
 * Auth layout — sade, ortalanmış, distraction-free.
 * @var string $__content
 * @var string $title
 */
$title = $title ?? 'Giriş Yap — Yemekhaneci';
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= e($title) ?></title>

<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext x='50' y='66' font-size='62' text-anchor='middle'%3E🍽️%3C/text%3E%3C/svg%3E">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" crossorigin="anonymous">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root { --brand-primary:#6B1F2A; --brand-accent:#C9A961; --brand-cream:#FAF6F0; --brand-ink:#1F1A17; }
  body {
    font-family: 'Manrope', system-ui, sans-serif;
    background: linear-gradient(140deg, #FAF6F0 0%, #F2E8D5 60%, #E8D9B8 100%);
    color: var(--brand-ink);
    min-height: 100vh;
  }
  .auth-shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
  .auth-card {
    width: 100%;
    max-width: 440px;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 20px 60px rgba(107,31,42,.18);
    overflow: hidden;
  }
  .auth-card .head {
    background: var(--brand-primary);
    color: #fff;
    text-align: center;
    padding: 2rem 1.5rem 1.5rem;
  }
  .auth-card .head .brand {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: -0.01em;
  }
  .auth-card .head .brand .dot { color: var(--brand-accent); }
  .auth-card .body { padding: 1.75rem; }
  .form-control { border-color: #DDD3C2; padding: .65rem .85rem; }
  .form-control:focus { border-color: var(--brand-accent); box-shadow: 0 0 0 .2rem rgba(201,169,97,.25); }
  .btn-brand { background: var(--brand-primary); border-color: var(--brand-primary); color:#fff; padding: .65rem 1rem; font-weight: 600; }
  .btn-brand:hover, .btn-brand:focus { background:#511620; border-color:#511620; color:#fff; }
  .demo-hint { background:#FAF6F0; border-left: 4px solid var(--brand-accent); padding:.85rem 1rem; border-radius: 6px; font-size:.92rem; }
  .demo-hint code { background: rgba(107,31,42,.06); padding: 1px 6px; border-radius: 4px; font-weight: 600; color: var(--brand-primary); }
</style>
</head>
<body>
  <div class="auth-shell">
    <div class="auth-card">
      <div class="head">
        <div class="brand">Yemekhaneci<span class="dot">.com.tr</span></div>
        <div class="opacity-75 small mt-1">Türkiye'nin Tarafsız Catering Pazaryeri</div>
      </div>
      <div class="body">
        <?= $__content ?? '' ?>
      </div>
    </div>
  </div>
</body>
</html>

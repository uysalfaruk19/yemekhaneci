<?php /** @var string $required_role */ ?>
<?= layout('app', '
<section class="py-5 py-lg-6 text-center">
  <div class="container">
    <div class="display-1 text-brand mb-2"><i class="bi bi-shield-lock"></i></div>
    <h1 class="display-5 mb-2">403 — Yetkisiz Erişim</h1>
    <p class="text-secondary col-lg-7 mx-auto">Bu sayfaya erişim yetkiniz yok. Doğru hesapla giriş yaptığınızdan emin olun.</p>
    <div class="text-secondary small mb-3">Bu sayfa <code>' . e($required_role) . '</code> rolü gerektiriyor.</div>
    <a href="/" class="btn btn-outline-brand me-2"><i class="bi bi-house me-1"></i>Anasayfa</a>
    <form method="post" action="/cikis-yap" class="d-inline">
      ' . csrf_field() . '
      <button class="btn btn-brand" type="submit"><i class="bi bi-arrow-left-right me-1"></i>Hesap değiştir</button>
    </form>
  </div>
</section>
', ['title' => '403 — Yetkisiz | Yemekhaneci', 'authed' => true, 'user' => \App\Auth\SimpleAuth::user()]);

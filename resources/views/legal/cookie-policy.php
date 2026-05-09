<section class="py-4 py-lg-5">
  <div class="container" style="max-width:820px;">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/" class="text-decoration-none">Ana sayfa</a></li>
        <li class="breadcrumb-item active" aria-current="page">Çerez Politikası</li>
      </ol>
    </nav>

    <h1 class="display-6 mb-2">Çerez Politikası</h1>
    <p class="text-secondary small">Son güncelleme: 9 Mayıs 2026 · v1.0</p>

    <p>Yemekhaneci.com.tr; oturum yönetimi, güvenlik ve kullanıcı tercihleri için çerez (cookie) kullanır.
    Çerezler; tarayıcınız tarafından saklanan küçük metin dosyalarıdır.</p>

    <h2 class="h5 mt-4">Kategoriler</h2>

    <div class="card card-soft p-3 mb-3">
      <strong>1. Kesinlikle Gerekli (zorunlu)</strong>
      <p class="small mb-1 text-secondary">Site çalışmazsa kullanıcı oturumu açılamaz. Onay aranmaz.</p>
      <ul class="small mb-0">
        <li><code>yemekhaneci_session</code> — Login session token (kapatınca silinir veya 2 saat)</li>
        <li><code>yh_csrf</code> — CSRF token (form güvenliği)</li>
        <li><code>yh_consent</code> — Çerez tercihiniz (1 yıl)</li>
      </ul>
    </div>

    <div class="card card-soft p-3 mb-3">
      <strong>2. İşlevsel (opt-in)</strong>
      <p class="small mb-1 text-secondary">Tercihlerinizi hatırlamak için. Reddederseniz site çalışır ama tercih kaybolur.</p>
      <ul class="small mb-0">
        <li><code>yh_theme</code> — Açık/karanlık mod tercihi (1 yıl)</li>
        <li><code>yh_lang</code> — Dil tercihi (1 yıl)</li>
      </ul>
    </div>

    <div class="card card-soft p-3 mb-3">
      <strong>3. Analitik (opt-in)</strong>
      <p class="small mb-1 text-secondary">Sayfa kullanım istatistikleri. Plausible (KVKK uyumlu, IP anonimleştirilmiş, üçüncü taraf paylaşım yok).</p>
      <ul class="small mb-0">
        <li>Henüz aktif değil — Faz 4'te eklenecek</li>
      </ul>
    </div>

    <div class="card card-soft p-3 mb-3">
      <strong>4. Pazarlama (opt-in)</strong>
      <p class="small mb-1 text-secondary">Reklam ve hedeflemeli içerik için.</p>
      <ul class="small mb-0">
        <li><strong>Şu an kullanılmamaktadır.</strong> Eklendiğinde bu sayfa güncellenir ve ek onay istenir.</li>
      </ul>
    </div>

    <h2 class="h5 mt-4">Tercihinizi Değiştirme</h2>
    <p>Çerez tercihinizi her sayfanın altındaki <strong>"Çerez tercihi"</strong> linkinden tekrar açarak değiştirebilirsiniz.
    Tarayıcı ayarlarından da çerezleri silebilirsiniz, ancak bu durumda site bazı özellikleri çalışmayabilir.</p>

    <h2 class="h5 mt-4">Üçüncü Taraf Çerezler</h2>
    <p>CDN (Cloudflare) edge cache yalnızca statik dosyalar için. Kullanıcı takibi yapılmaz.
    Bootstrap ve Alpine.js gibi kütüphaneler jsdelivr.net üzerinden gelir; çerez set etmezler.</p>

    <div class="text-center my-4">
      <button type="button" class="btn btn-brand" onclick="document.dispatchEvent(new CustomEvent('yh:open-cookie-prefs'))">
        <i class="bi bi-sliders me-1"></i>Çerez tercihimi değiştir
      </button>
    </div>
  </div>
</section>

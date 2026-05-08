<?php /** @var array $user */ ?>
<section class="py-4 py-lg-5">
  <div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
      <div>
        <span class="badge badge-soft mb-2"><i class="bi bi-shield-check me-1"></i>Yönetim Paneli</span>
        <h1 class="h3 mb-1">Hoş geldin, <?= e($user['display_name']) ?></h1>
        <p class="text-secondary mb-0 small">Yemekhaneci.com.tr — Faz 0.5 demo · 12 modül Faz 1+ ile gelecek.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="/araclar/enflasyon-hesaplayici" class="btn btn-outline-brand">
          <i class="bi bi-graph-up-arrow me-1"></i>Enflasyon Aracı
        </a>
        <form method="post" action="/cikis-yap" class="m-0">
          <?= csrf_field() ?>
          <button class="btn btn-light border" type="submit">
            <i class="bi bi-box-arrow-right me-1"></i>Çıkış
          </button>
        </form>
      </div>
    </div>

    <!-- KPI -->
    <div class="row g-3 mb-4">
      <div class="col-md-3 col-6"><div class="card card-soft p-3"><div class="text-secondary small">Aylık GMV</div><div class="h4 mono mb-0">— ₺</div><div class="small text-secondary mt-1">Faz 6+</div></div></div>
      <div class="col-md-3 col-6"><div class="card card-soft p-3"><div class="text-secondary small">Aktif Sipariş</div><div class="h4 mono mb-0">—</div><div class="small text-secondary mt-1">Faz 6+</div></div></div>
      <div class="col-md-3 col-6"><div class="card card-soft p-3"><div class="text-secondary small">Komisyon Geliri</div><div class="h4 mono mb-0">— ₺</div><div class="small text-secondary mt-1">İlk 6 ay sıfır</div></div></div>
      <div class="col-md-3 col-6"><div class="card card-soft p-3"><div class="text-secondary small">Aktif Yemekçi</div><div class="h4 mono mb-0">—</div><div class="small text-secondary mt-1">Faz 1+</div></div></div>
    </div>

    <div class="row g-3">
      <div class="col-lg-8">
        <div class="card card-soft">
          <div class="card-header bg-white">
            <h2 class="h5 mb-0"><i class="bi bi-grid-3x3-gap-fill me-2 text-brand"></i>Yönetim Modülleri (12 + 1)</h2>
          </div>
          <div class="list-group list-group-flush small">
            <div class="list-group-item d-flex justify-content-between"><span><strong>Dashboard</strong> · KPI + grafik + aktivite akışı</span><span class="badge bg-secondary">Faz 1+</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Canlı aktivite akışı</strong> · WebSocket / 5 sn polling</span><span class="badge bg-secondary">Faz 1+</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Yemekçi onayları (KYC)</strong> · 5 belge inceleme + onay/red</span><span class="badge bg-secondary">Faz 1</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Manuel yemekçi ekleme</strong> · Saha ekibinin telefonla anlaşma akışı</span><span class="badge bg-warning text-dark">Faz 3.5</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Kullanıcı yönetimi</strong> · 4 user_type + KVKK silme</span><span class="badge bg-secondary">Faz 1</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Sipariş ve teklif takibi</strong> · Filtre + timeline + müdahale</span><span class="badge bg-secondary">Faz 6</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Teklif Pivot (yemekçi × müşteri)</strong> · 3 boyutlu matris + dönüşüm hunisi</span><span class="badge bg-warning text-dark">Faz 3.5</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Anlaşmazlık yönetimi</strong> · Vaka detay + müşteri lehine iade</span><span class="badge bg-secondary">Faz 7</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Finansal yönetim</strong> · Komisyon + yemekçi alacak/borç + e-fatura</span><span class="badge bg-secondary">Faz 6</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>İçerik moderasyonu</strong> · Menü onayı + yorum kontrolü</span><span class="badge bg-secondary">Faz 5</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Pazarlama araçları</strong> · Kupon, kampanya, toplu iletişim</span><span class="badge bg-secondary">Faz 4+</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Sistem ayarları</strong> · Komisyon oranları, kategori, API key</span><span class="badge bg-secondary">Faz 1</span></div>
            <div class="list-group-item d-flex justify-content-between"><span><strong>Audit log (denetim izi)</strong> · 7 yıl saklama (KVKK)</span><span class="badge bg-secondary">Faz 1</span></div>
            <div class="list-group-item d-flex justify-content-between bg-light"><span><strong>Enflasyon kaynak yönetimi</strong> · TÜİK API + ENAG manuel + UYSA özel formülleri</span><span class="badge bg-success">Hazır (kaynak yönetim Faz 0.5.11)</span></div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card card-soft mb-3">
          <div class="card-body">
            <h3 class="h6"><i class="bi bi-stars text-accent me-2"></i>Demo Notları</h3>
            <ul class="small mb-0 ps-3">
              <li>Bu sayfa Faz 0.5 prototipidir (raw PHP, ADR-014).</li>
              <li>Faz 1.0a'da Laravel'e taşınacak; URL'ler ve görseller değişmeyecek.</li>
              <li>Şifreler Argon2id hash'li (CLAUDE.md kuralı).</li>
              <li>Demo hesaplar: <code>OFU/1234</code>, <code>Uysa/1234</code>.</li>
            </ul>
          </div>
        </div>

        <div class="card card-soft">
          <div class="card-body">
            <h3 class="h6"><i class="bi bi-rocket-takeoff text-brand me-2"></i>Sonraki Adımlar</h3>
            <ol class="small mb-0 ps-3">
              <li>TCMB EVDS API başvurusu (UYSA, e-Devlet)</li>
              <li>Composer install + Laravel 11 kurulum</li>
              <li>DB migration: Faz 1 (auth + supplier) tabloları</li>
              <li>Admin panel modülleri sırayla (Faz 1, sonra 3.5)</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

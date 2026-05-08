<?php /** @var array $user */ ?>
<section class="py-4 py-lg-5">
  <div class="container">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
      <div>
        <span class="badge badge-soft mb-2"><i class="bi bi-shop me-1"></i>Yemekçi Paneli</span>
        <h1 class="h3 mb-1">Hoş geldin, <?= e($user['display_name']) ?></h1>
        <p class="text-secondary mb-0 small">Yemekhaneci.com.tr — Faz 0.5 demo · Faz 2'de 6 sekmeli maliyet matrisi açılacak.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="/yemekci/araclar/enflasyon" class="btn btn-outline-brand">
          <i class="bi bi-graph-up-arrow me-1"></i>Enflasyon Hesaplayıcı
        </a>
        <form method="post" action="/cikis-yap" class="m-0">
          <?= csrf_field() ?>
          <button class="btn btn-light border" type="submit">
            <i class="bi bi-box-arrow-right me-1"></i>Çıkış
          </button>
        </form>
      </div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Bekleyen Talep</div>
          <div class="h3 mono mb-0">—</div>
          <div class="small text-secondary mt-1">Faz 7'de aktif</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Aktif Sipariş</div>
          <div class="h3 mono mb-0">—</div>
          <div class="small text-secondary mt-1">Faz 6'da aktif</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Bu Ay Ciro</div>
          <div class="h3 mono mb-0">— ₺</div>
          <div class="small text-secondary mt-1">Faz 6'da aktif</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card card-soft p-3">
          <div class="text-secondary small">Ortalama Puan</div>
          <div class="h3 mono mb-0">—</div>
          <div class="small text-secondary mt-1">Faz 7'de aktif</div>
        </div>
      </div>
    </div>

    <div class="card card-soft">
      <div class="card-header bg-white">
        <h2 class="h5 mb-0"><i class="bi bi-list-check me-2 text-brand"></i>Modüller (yapılacaklar)</h2>
      </div>
      <div class="list-group list-group-flush">
        <div class="list-group-item d-flex justify-content-between align-items-center">
          <div><strong>Firma profili & belgeler</strong><div class="text-secondary small">Faz 1'de aktif olacak (KYC akışı)</div></div>
          <span class="badge bg-secondary">Bekliyor</span>
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center">
          <div><strong>Maliyet & fiyatlandırma (6 sekme)</strong><div class="text-secondary small">Yemek, personel, sabit gider, ekipman, kar marjı, menü şablonu</div></div>
          <span class="badge bg-secondary">Faz 2</span>
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center">
          <div><strong>Menü kataloğu</strong><div class="text-secondary small">Standart menüler + görsel + arama</div></div>
          <span class="badge bg-secondary">Faz 5</span>
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center">
          <div><strong>Talepler ve teklifler</strong><div class="text-secondary small">Reaktif teklif, proaktif paket, B2B</div></div>
          <span class="badge bg-secondary">Faz 7</span>
        </div>
        <div class="list-group-item d-flex justify-content-between align-items-center">
          <div><strong>Müsaitlik takvimi</strong><div class="text-secondary small">Tarih bazlı kapasite blokları</div></div>
          <span class="badge bg-secondary">Faz 5</span>
        </div>
        <a href="/yemekci/araclar/enflasyon" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
          <div><strong>Enflasyon hesaplayıcı</strong><div class="text-secondary small">Maliyet güncelleme önerisi (3 panel ortak motor)</div></div>
          <span class="badge bg-success">Hazır <i class="bi bi-arrow-right ms-1"></i></span>
        </a>
      </div>
    </div>
  </div>
</section>

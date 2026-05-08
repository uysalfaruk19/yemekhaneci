<?php
/** @var bool $authed @var ?array $user */
?>
<section class="py-5 py-lg-6" style="background: linear-gradient(140deg,#FAF6F0 0%,#F2E8D5 60%,#E8D9B8 100%);">
  <div class="container">
    <div class="row align-items-center gy-4">
      <div class="col-lg-7">
        <span class="badge badge-soft mb-3"><i class="bi bi-stars me-1"></i>Türkiye'nin Tarafsız Catering Pazaryeri</span>
        <h1 class="display-4 mb-3">Yemekçinizi seçin, fiyatınızı görün, <span class="text-brand">karar verin</span>.</h1>
        <p class="lead text-secondary">Kurumsal ve bireysel müşteriler için onaylı yemekçi firmalarla şeffaf, hızlı ve kontrollü buluşturma. 100+ pilot yemekçi, ortalama fiyat motoru, anonim eşleştirme.</p>
        <div class="d-flex flex-wrap gap-2 mt-4">
          <a class="btn btn-brand btn-lg" href="#hizli-teklif">
            <i class="bi bi-lightning-charge-fill me-1"></i>Hızlı Teklif Al (60 sn)
          </a>
          <a class="btn btn-outline-brand btn-lg" href="/araclar/enflasyon-hesaplayici">
            <i class="bi bi-graph-up-arrow me-1"></i>Enflasyon Hesaplayıcı
          </a>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card card-soft p-4 p-lg-5">
          <h2 class="h5 mb-3"><i class="bi bi-shield-check text-brand me-2"></i>Neden Yemekhaneci?</h2>
          <ul class="list-unstyled mb-0 small">
            <li class="d-flex mb-3"><i class="bi bi-check-circle-fill text-brand me-2 mt-1"></i><div><strong>Tarafsız platform</strong><div class="text-secondary">UYSA sahip ama platforma sıradan bir yemekçi gibi katılır.</div></div></li>
            <li class="d-flex mb-3"><i class="bi bi-check-circle-fill text-brand me-2 mt-1"></i><div><strong>Maliyet tabanlı şeffaflık</strong><div class="text-secondary">Yemekçi maliyetini girer, müşteri ortalama görür.</div></div></li>
            <li class="d-flex"><i class="bi bi-check-circle-fill text-brand me-2 mt-1"></i><div><strong>Anonim eşleştirme</strong><div class="text-secondary">İsimler gizlenir, mail bırakırsanız açılır.</div></div></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="row gy-3">
      <div class="col-md-4">
        <div class="card card-soft h-100">
          <div class="card-body">
            <div class="display-6 text-brand mb-2"><i class="bi bi-people-fill"></i></div>
            <h3 class="h6">Müşteri için</h3>
            <p class="text-secondary small mb-2">9 soru wizard veya 60 saniyelik hızlı teklif modu ile anonim yemekçi listesi.</p>
            <a href="#" class="small text-brand">Yakında <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-soft h-100">
          <div class="card-body">
            <div class="display-6 text-brand mb-2"><i class="bi bi-shop"></i></div>
            <h3 class="h6">Yemekçi için</h3>
            <p class="text-secondary small mb-2">6 sekmeli maliyet matrisi, müsaitlik takvimi, mesajlaşma ve raporlama.</p>
            <a href="/giris-yap" class="small text-brand">Yemekçi girişi <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-soft h-100">
          <div class="card-body">
            <div class="display-6 text-brand mb-2"><i class="bi bi-graph-up-arrow"></i></div>
            <h3 class="h6">Enflasyon Hesaplayıcı</h3>
            <p class="text-secondary small mb-2">Geçmiş fiyatların bugünkü karşılığı — TÜİK, Gıda, Yİ-ÜFE, ENAG seçenekleriyle.</p>
            <a href="/araclar/enflasyon-hesaplayici" class="small text-brand">Hesapla <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section id="hizli-teklif" class="py-5 bg-white border-top">
  <div class="container text-center">
    <h2 class="display-6 mb-3">Hızlı Teklif Akışı <span class="text-secondary fs-6">(Faz 3'te aktif olacak)</span></h2>
    <p class="text-secondary col-lg-7 mx-auto">3 soru, 60 saniye, anonim 3 yemekçi listesi. Geliştirme aşamasında.</p>
    <div class="row g-3 justify-content-center mt-4">
      <div class="col-sm-6 col-md-3"><div class="card card-soft p-3"><strong>1. Kişi sayısı</strong><div class="small text-secondary">Slider 5–2000</div></div></div>
      <div class="col-sm-6 col-md-3"><div class="card card-soft p-3"><strong>2. Hangi öğün?</strong><div class="small text-secondary">Öğle / Akşam / Kumanya</div></div></div>
      <div class="col-sm-6 col-md-3"><div class="card card-soft p-3"><strong>3. Tarih + lokasyon</strong><div class="small text-secondary">Tek satır</div></div></div>
    </div>
  </div>
</section>

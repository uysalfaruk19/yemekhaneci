<?php
/**
 * @var array<int, array{code:string,name:string,color:string,base_period:string}> $sources
 * @var array $user
 */
?>
<section class="py-4 py-lg-5">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/yemekci" class="text-decoration-none">Yemekçi Paneli</a></li>
        <li class="breadcrumb-item"><span class="text-secondary">Araçlar</span></li>
        <li class="breadcrumb-item active" aria-current="page">Enflasyon Hesaplayıcı</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
      <div>
        <span class="badge badge-soft mb-2"><i class="bi bi-shop me-1"></i>Yemekçi Aracı</span>
        <h1 class="h3 mb-1">Maliyet Güncelleme — Enflasyon Hesaplayıcı</h1>
        <p class="text-secondary mb-0 small">Geçmiş aylarda belirlediğiniz menü fiyatlarının bugünkü karşılığını hesaplayıp maliyet matrisinizi güncelleyebilirsiniz.</p>
      </div>
      <a href="/yemekci" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i>Panele dön</a>
    </div>

    <div class="alert alert-light border d-flex align-items-start" role="note">
      <i class="bi bi-lightbulb-fill text-accent me-2 mt-1"></i>
      <div class="small">
        <strong>İpucu:</strong> "TÜİK Gıda Endeksi (TÜFE alt grubu)" catering ve yemek hizmetleri için en gerçekçi referanstır.
        Üretici taraflı maliyetlerde "TÜİK Yİ-ÜFE" daha doğru sonuç verir.
        <span class="text-secondary">Faz 2'de bu sonuç otomatik olarak maliyet matrisinize aktarılabilir olacak.</span>
      </div>
    </div>

    <?= view('partials.inflation-form', ['sources' => $sources, 'panel_origin' => 'supplier']) ?>
  </div>
</section>

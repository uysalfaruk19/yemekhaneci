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
        <li class="breadcrumb-item"><a href="/yonetim" class="text-decoration-none">Yönetim</a></li>
        <li class="breadcrumb-item"><span class="text-secondary">Araçlar</span></li>
        <li class="breadcrumb-item active" aria-current="page">Enflasyon Hesaplayıcı</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
      <div>
        <span class="badge badge-soft mb-2"><i class="bi bi-shield-check me-1"></i>Yönetim Aracı</span>
        <h1 class="h3 mb-1">Enflasyon Hesaplayıcı (Yönetim)</h1>
        <p class="text-secondary mb-0 small">Sektör analizleri, komisyon planlaması ve yemekçi denetimi için resmî ve özel UYSA endeksleriyle hesaplama.</p>
      </div>
      <div class="d-flex gap-2">
        <a href="/yonetim/sistem/enflasyon-kaynaklari" class="btn btn-outline-brand btn-sm">
          <i class="bi bi-database-gear me-1"></i>Kaynak Yönetimi
        </a>
        <a href="/yonetim" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i>Panele dön</a>
      </div>
    </div>

    <div class="alert alert-light border d-flex align-items-start" role="note">
      <i class="bi bi-info-circle-fill text-brand me-2 mt-1"></i>
      <div class="small">
        Bu modüle yalnızca admin rolü erişir. Hesap kayıtları <code>inflation_calculations</code> tablosuna
        <code>panel_origin='admin'</code> ile yazılır (DB Faz 1.0a sonrası aktif). Kaynak yönetiminden
        UYSA'ya özel endeksler (Et / Sebze / Süt vb.) tanımlanabilir.
      </div>
    </div>

    <?= view('partials.inflation-form', ['sources' => $sources, 'panel_origin' => 'admin']) ?>
  </div>
</section>

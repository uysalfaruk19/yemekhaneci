<?php
/** @var array<int, array{code:string,name:string,color:string,base_period:string}> $sources */
?>
<section class="py-4 py-lg-5" style="background: linear-gradient(140deg,#FAF6F0 0%,#F2E8D5 60%,#E8D9B8 100%);">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge badge-soft mb-2"><i class="bi bi-graph-up-arrow me-1"></i>Bağımsız Araç</span>
      <h1 class="display-5">Yemek Fiyatı Enflasyon Hesaplayıcı</h1>
      <p class="lead text-secondary col-lg-8 mx-auto">Geçmişte ödediğiniz catering veya yemek fiyatının bugünkü karşılığını TÜİK ve ENAG endeksleriyle saniyeler içinde hesaplayın.</p>
    </div>
  </div>
</section>

<section class="py-4 py-lg-5">
  <div class="container">
    <?= view('partials.inflation-form', ['sources' => $sources, 'panel_origin' => 'public']) ?>
  </div>
</section>

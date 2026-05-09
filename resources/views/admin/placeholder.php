<?php
/**
 * @var string $page_title
 * @var string $phase
 * @var string $icon
 * @var array<int,array<int,string>> $features
 */
$phaseColor = match (true) {
    str_contains($phase, 'Faz 1') => 'primary',
    str_contains($phase, 'Faz 3') => 'warning',
    str_contains($phase, 'Faz 4') => 'info',
    str_contains($phase, 'Faz 5') => 'secondary',
    str_contains($phase, 'Faz 6') => 'success',
    str_contains($phase, 'Faz 7') => 'danger',
    default                       => 'dark',
};
?>
<section class="py-4 py-lg-5">
  <div class="container" style="max-width: 880px;">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/yonetim" class="text-decoration-none">Yönetim</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($page_title) ?></li>
      </ol>
    </nav>

    <div class="text-center mb-4">
      <div class="display-1 text-brand mb-2"><i class="bi <?= e($icon) ?>"></i></div>
      <span class="badge bg-<?= e($phaseColor) ?>-subtle text-<?= e($phaseColor) ?>-emphasis fs-6 mb-2">
        <i class="bi bi-clock me-1"></i><?= e($phase) ?>'da aktif olacak
      </span>
      <h1 class="display-5"><?= e($page_title) ?></h1>
      <p class="lead text-secondary">Bu modül planlama aşamasındadır. Aşağıda kapsamı görebilirsiniz.</p>
    </div>

    <div class="card card-soft">
      <div class="card-header bg-white">
        <h2 class="h5 mb-0"><i class="bi bi-list-check text-brand me-2"></i>Planlanan Özellikler</h2>
      </div>
      <ul class="list-group list-group-flush">
        <?php foreach ($features as $feature): ?>
          <li class="list-group-item d-flex align-items-start">
            <i class="bi bi-circle-fill text-accent me-2 mt-1" style="font-size:.5rem;"></i>
            <span class="small"><?= e($feature[0]) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="alert alert-light border mt-3 small">
      <i class="bi bi-info-circle text-brand me-1"></i>
      <strong>Plan:</strong> Bu modül <code><?= e($phase) ?></code> kapsamında geliştirilecektir.
      PRD §6 (Yönetim Paneli) referans alınmıştır. Erken erişim isteyen demo için önceden
      iletişime geçebilirsiniz: <a href="mailto:dev@uysa.com.tr" class="text-decoration-none">dev@uysa.com.tr</a>.
    </div>

    <div class="text-center mt-4">
      <a href="/yonetim" class="btn btn-light border">
        <i class="bi bi-arrow-left me-1"></i>Yönetim ana sayfasına dön
      </a>
    </div>
  </div>
</section>

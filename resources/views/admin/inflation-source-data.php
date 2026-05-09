<?php
/**
 * Özel kaynak için aylık veri yönetimi (Faz 0.5.12).
 *
 * @var array<string,mixed> $record
 * @var array<string, array{value:float, notes:?string, entered_by:string, entered_at:string}> $monthly_values
 * @var ?string $flash_success
 * @var ?string $flash_error
 * @var array<string, array<int,string>> $errors
 */
$count = count($monthly_values);
?>
<section class="py-4 py-lg-5">
  <div class="container">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="/yonetim" class="text-decoration-none">Yönetim</a></li>
        <li class="breadcrumb-item"><a href="/yonetim/sistem/enflasyon-kaynaklari" class="text-decoration-none">Enflasyon Kaynakları</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($record['name']) ?> · Aylık Veri</li>
      </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
      <div>
        <span class="d-inline-block rounded me-2" style="width:14px;height:14px;background:<?= e($record['color_hex']) ?>;border:1px solid #ddd;vertical-align:middle;"></span>
        <span class="badge badge-soft mb-2"><i class="bi bi-stars me-1"></i>UYSA Özel Kaynak</span>
        <h1 class="h3 mb-1"><?= e($record['name']) ?></h1>
        <div class="text-secondary small">
          Kod: <code><?= e($record['code']) ?></code>
          · Birim: <code><?= e($record['unit']) ?></code>
          <?php if (!empty($record['base_period'])): ?> · Baz: <code><?= e($record['base_period']) ?></code><?php endif; ?>
          · <?= $count ?> aylık veri
        </div>
      </div>
      <div class="d-flex gap-2">
        <a href="/yonetim/sistem/enflasyon-kaynaklari/<?= e($record['code']) ?>/duzenle" class="btn btn-light border">
          <i class="bi bi-pencil me-1"></i>Kaynağı düzenle
        </a>
        <a href="/yonetim/sistem/enflasyon-kaynaklari" class="btn btn-light border">
          <i class="bi bi-arrow-left me-1"></i>Listeye dön
        </a>
      </div>
    </div>

    <?php if (!empty($flash_success)): ?>
      <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= e($flash_success) ?></div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
      <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($flash_error) ?></div>
    <?php endif; ?>

    <?php if (!empty($record['description'])): ?>
      <div class="alert alert-light border small">
        <i class="bi bi-info-circle text-brand me-1"></i><?= nl2br(e($record['description'])) ?>
      </div>
    <?php endif; ?>

    <div class="row g-4">
      <div class="col-lg-5">
        <div class="card card-soft">
          <div class="card-header bg-white">
            <h2 class="h6 mb-0"><i class="bi bi-calendar-plus text-brand me-2"></i>Yeni Aylık Veri Ekle</h2>
          </div>
          <div class="card-body">
            <form method="post" action="/yonetim/sistem/enflasyon-kaynaklari/<?= e($record['code']) ?>/aylik-veri" novalidate>
              <?= csrf_field() ?>

              <div class="mb-3">
                <label for="period" class="form-label">Ay <span class="text-brand">*</span></label>
                <input
                  type="month"
                  class="form-control <?= isset($errors['period']) ? 'is-invalid' : '' ?>"
                  id="period"
                  name="period"
                  value="<?= e(old('period', date('Y-m'))) ?>"
                  min="2003-01"
                  max="2099-12"
                  required>
                <?php if (isset($errors['period'])): ?>
                  <div class="invalid-feedback"><?= e($errors['period'][0]) ?></div>
                <?php else: ?>
                  <div class="form-text small">Aynı ay tekrar girilirse mevcut değer üstüne yazılır.</div>
                <?php endif; ?>
              </div>

              <div class="mb-3">
                <label for="value" class="form-label">Değer <span class="text-brand">*</span></label>
                <div class="input-group">
                  <input
                    type="text"
                    inputmode="decimal"
                    class="form-control mono <?= isset($errors['value']) ? 'is-invalid' : '' ?>"
                    id="value"
                    name="value"
                    value="<?= e(old('value', '')) ?>"
                    placeholder="örn: 285,50"
                    required>
                  <span class="input-group-text mono"><?= e($record['unit'] === 'index' ? 'pts' : $record['unit']) ?></span>
                  <?php if (isset($errors['value'])): ?>
                    <div class="invalid-feedback"><?= e($errors['value'][0]) ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div class="mb-3">
                <label for="notes" class="form-label">Not (isteğe bağlı)</label>
                <input
                  type="text"
                  class="form-control"
                  id="notes"
                  name="notes"
                  value="<?= e(old('notes', '')) ?>"
                  maxlength="500"
                  placeholder="örn: Kuzu but kg fiyatı, İstanbul ortalama">
              </div>

              <button type="submit" class="btn btn-brand w-100">
                <i class="bi bi-plus-lg me-1"></i>Veriyi Kaydet
              </button>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card card-soft">
          <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h2 class="h6 mb-0"><i class="bi bi-table text-brand me-2"></i>Girilmiş Veriler (<?= $count ?>)</h2>
            <?php if ($count >= 2): ?>
              <a href="/yonetim/araclar/enflasyon" class="btn btn-sm btn-outline-brand">
                <i class="bi bi-graph-up-arrow me-1"></i>Bu kaynakla hesaplama yap
              </a>
            <?php endif; ?>
          </div>
          <?php if ($count === 0): ?>
            <div class="card-body text-center text-secondary py-5">
              <i class="bi bi-calendar-x display-3 d-block mb-2 text-accent"></i>
              <strong>Henüz veri girilmemiş.</strong>
              <p class="small mb-0">Soldaki formdan en az 2 farklı ay için değer girerek hesaplamaya hazır hale getirin.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Ay</th>
                    <th class="text-end">Değer</th>
                    <th class="text-end">% Aylık</th>
                    <th class="text-end">% Yıllık</th>
                    <th>Not</th>
                    <th>Giriş</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    $previous = null;
                    foreach ($monthly_values as $period => $entry):
                      $monthlyPct = $previous === null ? null : (($entry['value'] / $previous) - 1) * 100;
                      [$y, $m] = array_map('intval', explode('-', $period));
                      $prevYearKey = sprintf('%04d-%02d', $y - 1, $m);
                      $yearlyPct = isset($monthly_values[$prevYearKey])
                        ? (($entry['value'] / $monthly_values[$prevYearKey]['value']) - 1) * 100
                        : null;
                  ?>
                    <tr>
                      <td class="mono"><?= e($period) ?></td>
                      <td class="mono text-end"><?= e(number_format($entry['value'], 4, ',', '.')) ?></td>
                      <td class="mono text-end"><?= $monthlyPct === null ? '—' : e(format_pct((float) $monthlyPct)) ?></td>
                      <td class="mono text-end"><?= $yearlyPct === null ? '—' : e(format_pct((float) $yearlyPct)) ?></td>
                      <td class="small text-secondary"><?= e($entry['notes'] ?? '') ?></td>
                      <td class="small text-secondary">
                        <?= e($entry['entered_by'] ?? '') ?><br>
                        <span style="font-size:.7rem;"><?= e($entry['entered_at'] ?? '') ?></span>
                      </td>
                      <td class="text-end">
                        <form method="post"
                              action="/yonetim/sistem/enflasyon-kaynaklari/<?= e($record['code']) ?>/aylik-veri/<?= e($period) ?>/sil"
                              class="d-inline"
                              onsubmit="return confirm('<?= e($period) ?> ayını silmek istediğinize emin misiniz?');">
                          <?= csrf_field() ?>
                          <button class="btn btn-sm btn-link text-danger p-0" title="Sil"><i class="bi bi-trash"></i></button>
                        </form>
                      </td>
                    </tr>
                  <?php $previous = (float) $entry['value']; endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

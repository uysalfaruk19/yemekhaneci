<?php
/**
 * Enflasyon hesaplayıcı paylaşılan partial — public/yemekçi/admin 3 panelinde de
 * aynı kullanılır. Sarmalayıcı sayfa kendi başlık/banner'ını ekler.
 *
 * @var array<int, array{code:string,name:string,color:string,base_period:string}> $sources
 * @var string $panel_origin  'public' | 'supplier' | 'admin'  — analytics + olası özel davranış
 */
$panelOrigin = $panel_origin ?? 'public';
$thisMonth = date('Y-m');
$twoYearsAgo = date('Y-m', strtotime('-24 months'));
?>
<div class="row gy-4" x-data="inflationApp()">
  <!-- Form -->
  <div class="col-lg-5">
    <div class="card card-soft">
      <div class="card-header bg-white border-0 pt-3 pb-0">
        <h2 class="h5 mb-0"><i class="bi bi-sliders me-2 text-brand"></i>Hesaplama Parametreleri</h2>
      </div>
      <div class="card-body">
        <form @submit.prevent="calculate()" novalidate>
          <input type="hidden" name="_csrf" :value="csrf">

          <div class="mb-3">
            <label class="form-label">Endeks kaynağı</label>
            <div class="row g-2">
              <?php foreach ($sources as $src): ?>
                <div class="col-6">
                  <label class="form-check card card-soft h-100 p-2 m-0" :class="{'border-2': form.source === '<?= e($src['code']) ?>'}" style="cursor:pointer;">
                    <input class="form-check-input ms-1 mt-1" type="radio" name="source" value="<?= e($src['code']) ?>" x-model="form.source">
                    <span class="ms-3">
                      <strong class="d-block small"><?= e($src['name']) ?></strong>
                      <span class="text-secondary" style="font-size:.78rem;">Baz: <?= e($src['base_period']) ?></span>
                    </span>
                  </label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label for="start_date_<?= e($panelOrigin) ?>" class="form-label">Başlangıç ayı</label>
              <input type="month" class="form-control" id="start_date_<?= e($panelOrigin) ?>" x-model="form.start_date" min="2020-01" :max="thisMonth" required>
            </div>
            <div class="col-md-6">
              <label for="end_date_<?= e($panelOrigin) ?>" class="form-label">Hedef ay</label>
              <input type="month" class="form-control" id="end_date_<?= e($panelOrigin) ?>" x-model="form.end_date" min="2020-01" :max="thisMonth" required>
            </div>
          </div>

          <div class="mb-3">
            <label for="start_price_<?= e($panelOrigin) ?>" class="form-label">Başlangıç fiyatı (₺)</label>
            <div class="input-group">
              <span class="input-group-text">₺</span>
              <input type="text" inputmode="decimal" class="form-control mono" id="start_price_<?= e($panelOrigin) ?>" x-model="form.start_price" placeholder="Örn: 5.000,00" required>
            </div>
          </div>

          <button type="submit" class="btn btn-brand w-100" :disabled="loading">
            <template x-if="!loading"><span><i class="bi bi-calculator me-1"></i>Hesapla</span></template>
            <template x-if="loading"><span><span class="spinner-border spinner-border-sm me-2"></span>Hesaplanıyor…</span></template>
          </button>

          <div class="text-secondary small mt-3">
            <i class="bi bi-shield-lock me-1"></i>Veriler Faz 0.5 demo aşamasında sentetik aylık serilerden hesaplanır. Faz 0.5.7'de TCMB EVDS API ile gerçek veriye geçilecek.
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Sonuç + Grafik -->
  <div class="col-lg-7">
    <div class="card card-soft h-100">
      <div class="card-body">
        <template x-if="!result && !error">
          <div class="text-center text-secondary py-5 my-5">
            <i class="bi bi-graph-up-arrow display-1 d-block mb-3 text-accent"></i>
            <h3 class="h5">Sonuç burada görünecek</h3>
            <p class="small">Soldan bir endeks ve tarih aralığı seçip "Hesapla" butonuna basın.</p>
          </div>
        </template>

        <template x-if="error">
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Hata:</strong> <span x-text="error"></span>
          </div>
        </template>

        <template x-if="result">
          <div>
            <template x-if="result.warning">
              <div class="alert alert-warning small py-2"><i class="bi bi-info-circle me-1"></i><span x-text="result.warning"></span></div>
            </template>

            <?php if ($panelOrigin === 'public'): ?>
            <div class="card border-0 mb-3" style="background:#FAF6F0;">
              <div class="card-body p-3">
                <template x-if="!leadSent">
                  <form @submit.prevent="sendLead()">
                    <strong class="d-block mb-2"><i class="bi bi-envelope-paper-fill text-brand me-1"></i>Sonucu e-postama gönder</strong>
                    <div class="row g-2">
                      <div class="col-sm-7">
                        <input type="email" class="form-control form-control-sm" x-model="lead.email" required placeholder="ornek@firma.com.tr">
                      </div>
                      <div class="col-sm-5">
                        <button type="submit" class="btn btn-brand btn-sm w-100" :disabled="leadLoading || !lead.kvkk">
                          <template x-if="!leadLoading"><span><i class="bi bi-send me-1"></i>Gönder</span></template>
                          <template x-if="leadLoading"><span><span class="spinner-border spinner-border-sm me-1"></span>Gönderiliyor…</span></template>
                        </button>
                      </div>
                    </div>
                    <div class="form-check small mt-2">
                      <input class="form-check-input" type="checkbox" id="kvkk_<?= e($panelOrigin) ?>" x-model="lead.kvkk" required>
                      <label class="form-check-label text-secondary" for="kvkk_<?= e($panelOrigin) ?>">
                        <a href="#" class="text-brand text-decoration-none">Aydınlatma metnini</a> okudum,
                        e-posta adresimin hesap sonucu ve UYSA güncellemeleri için kullanılmasını kabul ediyorum.
                      </label>
                    </div>
                    <div class="text-danger small mt-1" x-show="leadError" x-text="leadError"></div>
                  </form>
                </template>
                <template x-if="leadSent">
                  <div class="text-success small mb-0">
                    <i class="bi bi-check-circle-fill me-1"></i><span x-text="leadMessage"></span>
                  </div>
                </template>
              </div>
            </div>
            <?php endif; ?>

            <div class="row g-3 mb-3">
              <div class="col-sm-6">
                <div class="text-secondary small">Başlangıç (<span x-text="result.start_period"></span>)</div>
                <div class="h4 mono mb-0" x-text="formatMoney(result.start_price)"></div>
              </div>
              <div class="col-sm-6">
                <div class="text-secondary small">Bugünkü karşılığı (<span x-text="result.end_period"></span>)</div>
                <div class="h4 mono text-brand mb-0" x-text="formatMoney(result.end_price)"></div>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-sm-6">
                <div class="card bg-cream border-0 p-3">
                  <div class="text-secondary small">Toplam değişim</div>
                  <div class="h5 mono mb-0" x-text="formatPct(result.change_pct)"></div>
                </div>
              </div>
              <div class="col-sm-6">
                <div class="card bg-cream border-0 p-3">
                  <div class="text-secondary small">Aylık ortalama bileşik</div>
                  <div class="h5 mono mb-0" x-text="formatPct(result.monthly_avg_pct)"></div>
                </div>
              </div>
            </div>

            <div class="bg-white border rounded p-2">
              <canvas id="inflChart_<?= e($panelOrigin) ?>" height="160"></canvas>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</div>

<script>
function inflationApp() {
  return {
    csrf: '<?= e(csrf_token()) ?>',
    panelOrigin: '<?= e($panelOrigin) ?>',
    thisMonth: '<?= e($thisMonth) ?>',
    canvasId: 'inflChart_<?= e($panelOrigin) ?>',
    form: {
      source: 'tuik_tufe_gida',
      start_date: '<?= e($twoYearsAgo) ?>',
      end_date: '<?= e($thisMonth) ?>',
      start_price: '5.000,00',
    },
    loading: false,
    result: null,
    error: null,
    chart: null,

    // Lead capture (sadece public panelde aktif)
    lead: { email: '', kvkk: false },
    leadLoading: false,
    leadSent: false,
    leadError: null,
    leadMessage: '',

    async calculate() {
      this.loading = true;
      this.error = null;
      try {
        const fd = new FormData();
        fd.set('_csrf', this.csrf);
        fd.set('source', this.form.source);
        fd.set('start_date', this.form.start_date);
        fd.set('end_date', this.form.end_date);
        fd.set('start_price', this.form.start_price);
        fd.set('panel_origin', this.panelOrigin);

        const res = await fetch('/api/v1/enflasyon/hesapla', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) {
          const firstField = data.errors ? Object.values(data.errors)[0]?.[0] : null;
          this.error = firstField || data.message || 'Beklenmedik hata.';
          this.result = null;
        } else {
          this.result = data.data;
          this.$nextTick(() => this.drawChart());
        }
      } catch (e) {
        this.error = 'Sunucuya ulaşılamadı: ' + e.message;
        this.result = null;
      } finally {
        this.loading = false;
      }
    },

    drawChart() {
      if (!this.result) return;
      if (this.chart) this.chart.destroy();
      const canvas = document.getElementById(this.canvasId);
      if (!canvas || !window.Chart) return;
      const labels = this.result.monthly_series.map(p => p.period);
      const values = this.result.monthly_series.map(p => p.index);
      this.chart = new Chart(canvas, {
        type: 'line',
        data: {
          labels,
          datasets: [{
            label: 'Endeks değeri',
            data: values,
            borderColor: '#6B1F2A',
            backgroundColor: 'rgba(201,169,97,.15)',
            tension: 0.25,
            fill: true,
            pointRadius: 0,
          }],
        },
        options: {
          responsive: true,
          plugins: { legend: { display: false } },
          scales: {
            x: { ticks: { maxTicksLimit: 8 } },
            y: { beginAtZero: false },
          },
        },
      });
    },

    async sendLead() {
      if (!this.result) {
        this.leadError = 'Önce bir hesaplama yapın.';
        return;
      }
      if (!this.lead.kvkk) {
        this.leadError = 'KVKK onayı zorunludur.';
        return;
      }
      this.leadLoading = true;
      this.leadError = null;
      try {
        const fd = new FormData();
        fd.set('_csrf', this.csrf);
        fd.set('email', this.lead.email);
        fd.set('kvkk', '1');
        fd.set('source', this.form.source);
        fd.set('start_date', this.form.start_date);
        fd.set('end_date', this.form.end_date);
        fd.set('start_price', this.form.start_price);
        fd.set('panel_origin', this.panelOrigin);

        const res = await fetch('/api/v1/enflasyon/mail-gonder', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) {
          const firstField = data.errors ? Object.values(data.errors)[0]?.[0] : null;
          this.leadError = firstField || data.message || 'Beklenmedik hata.';
        } else {
          this.leadSent = true;
          this.leadMessage = data.data?.message || 'Talebiniz alındı.';
        }
      } catch (e) {
        this.leadError = 'Sunucuya ulaşılamadı: ' + e.message;
      } finally {
        this.leadLoading = false;
      }
    },

    formatMoney(v) {
      return Number(v).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
    },
    formatPct(v) {
      const sign = v > 0 ? '+' : '';
      return sign + Number(v).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
    },
  };
}
</script>

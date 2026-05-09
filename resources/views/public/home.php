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

<section id="hizli-teklif" class="py-5 bg-white border-top" x-data="quickQuoteWizard()">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge badge-soft mb-2"><i class="bi bi-lightning-charge-fill me-1"></i>60 saniyede teklif</span>
      <h2 class="display-6 mb-2">Hızlı Teklif Al</h2>
      <p class="text-secondary col-lg-7 mx-auto">3 soru, 60 saniye, anonim 3 yemekçi tarafsız teklif. Sözleşme ve mesajlaşma platform üzerinden.</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-8">
        <!-- Adım göstergesi -->
        <div class="d-flex justify-content-between mb-3" x-show="!submitted">
          <template x-for="(label, idx) in steps" :key="idx">
            <div class="flex-fill text-center" :class="{ 'text-brand fw-bold': step === idx + 1, 'text-secondary': step !== idx + 1 }">
              <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-1"
                   style="width:32px;height:32px;"
                   :class="step >= idx + 1 ? 'bg-brand text-white' : 'bg-light text-secondary'">
                <span x-text="idx + 1"></span>
              </div>
              <div class="small" x-text="label"></div>
            </div>
          </template>
        </div>

        <div class="card card-soft" x-show="!submitted">
          <div class="card-body p-4">
            <form @submit.prevent="next()" novalidate>
              <input type="hidden" name="_csrf" :value="csrf">

              <!-- ADIM 1: Kişi sayısı -->
              <div x-show="step === 1">
                <h3 class="h5">Kaç kişilik?</h3>
                <p class="text-secondary small">Slider'ı çekin veya kutuya yazın.</p>
                <div class="d-flex align-items-center gap-3 my-3">
                  <input type="range" class="form-range flex-fill" min="5" max="2000" step="5" x-model.number="form.guest_count">
                  <input type="number" class="form-control mono" style="width:120px;" min="5" max="5000" x-model.number="form.guest_count">
                </div>
                <div class="text-secondary small">Tipik aralıklar: 50 (toplantı) · 250 (etkinlik) · 1000 (fabrika)</div>
                <div class="text-danger small mt-2" x-show="errors.guest_count" x-text="errors.guest_count"></div>
              </div>

              <!-- ADIM 2: Öğün -->
              <div x-show="step === 2">
                <h3 class="h5">Hangi öğün?</h3>
                <p class="text-secondary small">Birden fazla seçim için "Detaylı talep" akışına geçebilirsiniz.</p>
                <div class="row g-2 mt-3">
                  <template x-for="(label, key) in mealTypes" :key="key">
                    <div class="col-sm-6 col-lg-3">
                      <label class="card card-soft p-3 m-0 h-100" style="cursor:pointer;"
                             :class="{ 'border-2 bg-cream': form.meal_type === key }">
                        <input type="radio" class="form-check-input" name="meal_type" :value="key" x-model="form.meal_type">
                        <span class="ms-2 fw-bold" x-text="label"></span>
                      </label>
                    </div>
                  </template>
                </div>
                <div class="text-danger small mt-2" x-show="errors.meal_type" x-text="errors.meal_type"></div>
              </div>

              <!-- ADIM 3: Tarih + lokasyon + iletişim -->
              <div x-show="step === 3">
                <h3 class="h5">Ne zaman ve nerede?</h3>
                <div class="row g-2 mt-3">
                  <div class="col-md-4">
                    <label class="form-label small">Tarih</label>
                    <input type="date" class="form-control" x-model="form.event_date" :min="today" required>
                    <div class="text-danger small mt-1" x-show="errors.event_date" x-text="errors.event_date"></div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small">Şehir</label>
                    <input type="text" class="form-control" x-model="form.city" placeholder="İstanbul" required>
                    <div class="text-danger small mt-1" x-show="errors.city" x-text="errors.city"></div>
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small">İlçe (opsiyonel)</label>
                    <input type="text" class="form-control" x-model="form.district" placeholder="Şişli">
                  </div>
                </div>

                <hr class="my-3">

                <h3 class="h6 mb-2">İletişim</h3>
                <p class="text-secondary small mb-3">Yemekçilerin teklifini size iletebilmemiz için en az birini girin (e-posta veya telefon).</p>
                <div class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label small">İsim (opsiyonel)</label>
                    <input type="text" class="form-control" x-model="form.contact_name" placeholder="Ad Soyad">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">E-posta</label>
                    <input type="email" class="form-control" x-model="form.contact_email" placeholder="ornek@firma.com.tr">
                    <div class="text-danger small mt-1" x-show="errors.contact_email" x-text="errors.contact_email"></div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">Telefon</label>
                    <input type="tel" class="form-control" x-model="form.contact_phone" placeholder="0532 123 45 67">
                    <div class="text-danger small mt-1" x-show="errors.contact_phone" x-text="errors.contact_phone"></div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">Notlar (opsiyonel)</label>
                    <input type="text" class="form-control" x-model="form.notes" placeholder="Vejetaryen menü, glütensiz seçenek..." maxlength="500">
                  </div>
                </div>

                <div class="text-danger small mt-2" x-show="errors.contact" x-text="errors.contact"></div>

                <div class="form-check mt-3">
                  <input class="form-check-input" type="checkbox" id="kvkk_quote" x-model="form.kvkk" required>
                  <label class="form-check-label small" for="kvkk_quote">
                    <a href="/yasal/aydinlatma-metni" target="_blank" class="text-brand text-decoration-none">Aydınlatma metnini</a> okudum,
                    iletişim bilgilerimin yemekçilerle paylaşılmasını ve UYSA'nın geri dönüş yapmasını kabul ediyorum.
                  </label>
                </div>
                <div class="text-danger small mt-1" x-show="errors.kvkk" x-text="errors.kvkk"></div>
              </div>

              <!-- Navigasyon -->
              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-light border" @click="prev()" x-show="step > 1">
                  <i class="bi bi-arrow-left me-1"></i>Geri
                </button>
                <div></div>
                <button type="submit" class="btn btn-brand" :disabled="loading">
                  <template x-if="step < 3"><span>İleri <i class="bi bi-arrow-right ms-1"></i></span></template>
                  <template x-if="step === 3 && !loading"><span><i class="bi bi-send-fill me-1"></i>Talebi Gönder</span></template>
                  <template x-if="loading"><span><span class="spinner-border spinner-border-sm me-2"></span>Gönderiliyor…</span></template>
                </button>
              </div>

              <div class="text-danger small mt-2" x-show="formError" x-text="formError"></div>
            </form>
          </div>
        </div>

        <!-- Başarı -->
        <div class="card card-soft" x-show="submitted">
          <div class="card-body text-center p-5">
            <i class="bi bi-check-circle-fill display-1 text-brand"></i>
            <h3 class="display-6 my-3">Talebiniz alındı!</h3>
            <p class="lead">Referans no: <code class="text-brand mono fs-5" x-text="reference"></code></p>
            <p class="text-secondary" x-text="successMessage"></p>
            <ol class="text-start col-lg-9 mx-auto mt-4">
              <template x-for="step in nextSteps" :key="step">
                <li class="mb-2 small" x-text="step"></li>
              </template>
            </ol>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function quickQuoteWizard() {
  return {
    csrf: '<?= e(csrf_token()) ?>',
    today: new Date().toISOString().slice(0, 10),
    step: 1,
    steps: ['Kişi', 'Öğün', 'Tarih + iletişim'],
    mealTypes: { ogle: 'Öğle', aksam: 'Akşam', kumanya: 'Kumanya', cocktail: 'Cocktail' },
    form: {
      guest_count: 100,
      meal_type: 'ogle',
      event_date: '',
      city: '',
      district: '',
      contact_name: '',
      contact_email: '',
      contact_phone: '',
      notes: '',
      kvkk: false,
    },
    errors: {},
    formError: null,
    loading: false,
    submitted: false,
    reference: '',
    successMessage: '',
    nextSteps: [],

    next() {
      this.errors = {};
      if (this.step === 1) {
        if (this.form.guest_count < 5 || this.form.guest_count > 5000) {
          this.errors.guest_count = 'Kişi sayısı 5-5000 arasında olmalı.';
          return;
        }
        this.step = 2;
      } else if (this.step === 2) {
        if (!this.form.meal_type) {
          this.errors.meal_type = 'Bir öğün seçin.';
          return;
        }
        this.step = 3;
      } else {
        this.submit();
      }
    },

    prev() {
      this.errors = {};
      if (this.step > 1) this.step--;
    },

    async submit() {
      this.loading = true;
      this.errors = {};
      this.formError = null;
      try {
        const fd = new FormData();
        fd.set('_csrf', this.csrf);
        for (const [k, v] of Object.entries(this.form)) {
          fd.set(k, k === 'kvkk' ? (v ? '1' : '') : String(v));
        }
        const res = await fetch('/api/v1/hizli-teklif', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) {
          if (data.errors) {
            for (const [k, msgs] of Object.entries(data.errors)) {
              this.errors[k] = msgs[0];
            }
          }
          this.formError = data.message || 'Form gönderilemedi.';
        } else {
          this.submitted = true;
          this.reference = data.data.reference;
          this.successMessage = data.data.message;
          this.nextSteps = data.data.next_steps || [];
        }
      } catch (e) {
        this.formError = 'Sunucuya ulaşılamadı: ' + e.message;
      } finally {
        this.loading = false;
      }
    },
  };
}
</script>

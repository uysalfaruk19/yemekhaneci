<?php /** @var bool $authed @var ?array $user */ ?>
<section class="py-5 py-lg-6" style="background: linear-gradient(140deg,#FAF6F0 0%,#F2E8D5 60%,#E8D9B8 100%);">
  <div class="container">
    <div class="row align-items-center gy-4">
      <div class="col-lg-7">
        <span class="badge badge-soft mb-3"><i class="bi bi-stars me-1"></i>Türkiye'nin Tarafsız Catering Pazaryeri</span>
        <h1 class="display-4 mb-3">Yemekhanenizin fiyatını <span class="text-brand">30 saniyede</span> öğrenin.</h1>
        <p class="lead text-secondary">9 soru, anonim yemekçi listesi, bölgenizin gerçek ortalama fiyatı. KVKK uyumlu, mail ile detaylı teklif.</p>
        <div class="d-flex flex-wrap gap-2 mt-4">
          <a class="btn btn-brand btn-lg" href="#hizli-teklif">
            <i class="bi bi-lightning-charge-fill me-1"></i>Hızlı Teklif Al
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
            <p class="text-secondary small mb-2">9 soru wizard ile bölgenizdeki ortalama fiyat + anonim 3-5 yemekçi listesi.</p>
            <a href="#hizli-teklif" class="small text-brand">Hemen başla <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card card-soft h-100">
          <div class="card-body">
            <div class="display-6 text-brand mb-2"><i class="bi bi-shop"></i></div>
            <h3 class="h6">Yemekçi için</h3>
            <p class="text-secondary small mb-2">6 sekmeli maliyet matrisi, müsaitlik takvimi, mesajlaşma ve raporlama.</p>
            <a href="/yemekci-ol" class="small text-brand">Yemekçi olarak başvur <i class="bi bi-arrow-right"></i></a>
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

<!-- ════════════════════════════════════════════════════════════════════
     HIZLI TEKLİF — 9 SORU WIZARD (PRD §7.2)
     ════════════════════════════════════════════════════════════════════ -->
<section id="hizli-teklif" class="py-5 bg-white border-top" x-data="quickQuoteWizard()">
  <div class="container">
    <div class="text-center mb-4">
      <span class="badge badge-soft mb-2"><i class="bi bi-lightning-charge-fill me-1"></i>9 soru · 60 saniye</span>
      <h2 class="display-6 mb-2">Yemekhanenizin Fiyatını Hesaplayın</h2>
      <p class="text-secondary col-lg-7 mx-auto">İhtiyaçlarınızı belirtin, bölgenizdeki yemekçilerin ortalama fiyatlarını anında görün.</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-9">

        <!-- Adım göstergesi -->
        <div class="mb-3" x-show="!submitted">
          <div class="d-flex justify-content-between small text-secondary mb-1">
            <strong x-text="`Adım ${step}/9 — ${steps[step-1]}`"></strong>
            <span x-text="`${Math.round((step / 9) * 100)}%`"></span>
          </div>
          <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-brand" :style="`width: ${(step / 9) * 100}%`"></div>
          </div>
        </div>

        <div class="card card-soft" x-show="!submitted">
          <div class="card-body p-4">
            <form @submit.prevent="next()" novalidate>
              <input type="hidden" name="_csrf" :value="csrf">

              <!-- ════════════ S1: KİŞİ SAYISI ════════════ -->
              <div x-show="step === 1">
                <h3 class="h5 mb-1">Kaç kişilik yemek hizmeti?</h3>
                <p class="text-secondary small mb-3">Slider ile veya kutuya yazarak girin.</p>
                <div class="d-flex align-items-center gap-3 my-3">
                  <input type="range" class="form-range flex-fill" min="1" max="2000" step="1" x-model.number="form.guest_count" @input="syncOgleFromGuest()">
                  <input type="number" class="form-control mono" style="width:140px;" min="1" max="10000" x-model.number="form.guest_count" @input="syncOgleFromGuest()">
                </div>
                <div class="text-secondary small">Tipik aralıklar: 50 (toplantı) · 250 (etkinlik) · 1.000+ (fabrika)</div>
                <div class="text-danger small mt-2" x-show="errors.guest_count" x-text="errors.guest_count"></div>
              </div>

              <!-- ════════════ S2: ÖĞÜN DAĞILIMI ════════════ -->
              <div x-show="step === 2">
                <h3 class="h5 mb-1">Hangi öğünler için?</h3>
                <p class="text-secondary small mb-3">Birden fazla öğün seçebilirsiniz; her biri için kişi sayısı farklı olabilir.</p>
                <div class="row g-2">
                  <template x-for="key in mealKeys" :key="key">
                    <div class="col-md-4">
                      <label class="card card-soft p-3 m-0 h-100" style="cursor:pointer;"
                             :class="{'border-2 bg-cream': form.meals[key] > 0}">
                        <div class="d-flex align-items-center gap-2 mb-2">
                          <input type="checkbox" class="form-check-input m-0" :checked="form.meals[key] > 0"
                                 @change="toggleMeal(key, $event.target.checked)">
                          <strong x-text="mealLabels[key]"></strong>
                        </div>
                        <input type="number" class="form-control form-control-sm mono"
                               x-model.number="form.meals[key]" min="0" max="10000"
                               @input="recalcGuestFromMeals()" placeholder="Kişi sayısı">
                      </label>
                    </div>
                  </template>
                </div>
                <div class="alert alert-light border small mt-3 mb-0">
                  <i class="bi bi-info-circle text-brand me-1"></i>
                  Toplam: <strong x-text="totalMeals"></strong> öğün/gün ·
                  Kişi sayısı: <strong x-text="form.guest_count"></strong> · ekran otomatik dengeler.
                </div>
                <div class="text-danger small mt-2" x-show="errors.meals" x-text="errors.meals"></div>
              </div>

              <!-- ════════════ S3: MENÜ YAPISI ════════════ -->
              <div x-show="step === 3">
                <h3 class="h5 mb-1">Menüde ne olsun?</h3>
                <p class="text-secondary small mb-3">Çeşit sayısına göre segment otomatik belirlenir.</p>

                <!-- Canlı menü önizlemesi -->
                <div class="card bg-cream border-0 p-2 mb-3">
                  <div class="small text-secondary">Canlı önizleme:</div>
                  <div class="d-flex flex-wrap gap-1 mt-1">
                    <template x-for="pill in menuPreview" :key="pill">
                      <span class="badge bg-white border text-dark" x-text="pill"></span>
                    </template>
                    <span class="badge ms-auto" :class="`bg-${segmentColor()}-subtle text-${segmentColor()}-emphasis`"
                          x-text="`segment: ${menuSegment()}`"></span>
                  </div>
                </div>

                <!-- Sabit bileşenler -->
                <div class="row g-2 mb-3">
                  <template x-for="item in fixedItems" :key="item.key">
                    <div class="col-6 col-md-3">
                      <div class="form-check">
                        <input type="checkbox" class="form-check-input" :id="`m_${item.key}`" x-model="form.menu[item.key]">
                        <label class="form-check-label small" :for="`m_${item.key}`" x-text="item.label"></label>
                      </div>
                    </div>
                  </template>
                </div>

                <!-- Salata bar slider -->
                <div class="mb-3">
                  <label class="form-label small d-flex justify-content-between">
                    <span>Salata bar çeşit sayısı</span>
                    <strong x-text="`${form.menu.salad_bar_count} çeşit`"></strong>
                  </label>
                  <input type="range" class="form-range" min="0" max="7" step="1" x-model.number="form.menu.salad_bar_count">
                </div>

                <!-- Tatlı + İçecek -->
                <div class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label small">Tatlı / Meyve</label>
                    <select class="form-select form-select-sm" x-model="form.menu.dessert">
                      <option value="none">Yok</option>
                      <option value="fruit">Meyve</option>
                      <option value="dessert">Tatlı</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">Ayran / Yoğurt</label>
                    <select class="form-select form-select-sm" x-model="form.menu.drinks">
                      <option value="rotation">Rotasyon (gün gün)</option>
                      <option value="ayran">Sadece ayran</option>
                      <option value="yogurt">Sadece yoğurt</option>
                      <option value="both">Birlikte (her gün ikisi)</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- ════════════ S4: HİZMET SEGMENTİ ════════════ -->
              <div x-show="step === 4">
                <h3 class="h5 mb-1">Hizmet segmenti</h3>
                <p class="text-secondary small mb-3">Maliyet matrisinde fiyat satırını belirler.</p>
                <div class="row g-3">
                  <template x-for="seg in segments" :key="seg.key">
                    <div class="col-md-4">
                      <label class="card card-soft p-3 m-0 h-100 text-center" style="cursor:pointer;"
                             :class="{'border-2 bg-cream': form.segment === seg.key}">
                        <input type="radio" class="form-check-input visually-hidden" name="segment"
                               :value="seg.key" x-model="form.segment">
                        <div class="display-5" x-text="seg.icon"></div>
                        <strong x-text="seg.label"></strong>
                        <div class="small text-secondary" x-text="seg.desc"></div>
                      </label>
                    </div>
                  </template>
                </div>
              </div>

              <!-- ════════════ S5: HİZMET LOKASYONU ════════════ -->
              <div x-show="step === 5">
                <h3 class="h5 mb-1">Hizmet lokasyonu</h3>
                <p class="text-secondary small mb-3">Yemekçinin hizmet bölgesi poligonu ile eşleştirilir.</p>
                <div class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label small">Şehir <span class="text-brand">*</span></label>
                    <input type="text" class="form-control" x-model="form.location.city" placeholder="İstanbul">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">İlçe</label>
                    <input type="text" class="form-control" x-model="form.location.district" placeholder="Şişli">
                  </div>
                  <div class="col-12">
                    <label class="form-label small">Adres / mahalle (opsiyonel)</label>
                    <input type="text" class="form-control" x-model="form.location.address" placeholder="Mecidiyeköy, Büyükdere Cad. No:1">
                  </div>
                </div>
                <div class="text-danger small mt-2" x-show="errors.city" x-text="errors.city"></div>
              </div>

              <!-- ════════════ S6: PERSONEL ════════════ -->
              <div x-show="step === 6">
                <h3 class="h5 mb-1">Personel desteği gerekli mi?</h3>
                <p class="text-secondary small mb-3">Yemekçi personelinin sahaya gelmesi gerekiyor mu?</p>
                <div class="form-check form-switch mb-3">
                  <input type="checkbox" class="form-check-input" id="personnel_toggle" x-model="form.personnel.enabled" role="switch">
                  <label class="form-check-label" for="personnel_toggle">
                    <strong x-text="form.personnel.enabled ? 'Evet, personel istiyorum' : 'Hayır, sadece yemek tedarik'"></strong>
                  </label>
                </div>
                <div x-show="form.personnel.enabled" class="row g-2 mt-2">
                  <div class="col-md-4">
                    <label class="form-label small">Aşçı sayısı</label>
                    <input type="number" class="form-control mono" min="0" max="20" x-model.number="form.personnel.cooks">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small">Servis personeli</label>
                    <input type="number" class="form-control mono" min="0" max="50" x-model.number="form.personnel.service">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label small">Bulaşık / temizlik</label>
                    <input type="number" class="form-control mono" min="0" max="20" x-model.number="form.personnel.cleaning">
                  </div>
                </div>
              </div>

              <!-- ════════════ S7: EKİPMAN ════════════ -->
              <div x-show="step === 7">
                <h3 class="h5 mb-1">Mutfak yatırımı gerekli mi?</h3>
                <p class="text-secondary small mb-3">Yemekçinin sahada kuracağı ekipman (12-36 ay amortisman ile fiyata yansır).</p>
                <div class="form-check form-switch mb-3">
                  <input type="checkbox" class="form-check-input" id="equipment_toggle" x-model="form.equipment.enabled" role="switch">
                  <label class="form-check-label" for="equipment_toggle">
                    <strong x-text="form.equipment.enabled ? 'Evet, ekipman gerekli' : 'Hayır, mutfak hazır'"></strong>
                  </label>
                </div>
                <div x-show="form.equipment.enabled" class="row g-2 mt-2">
                  <template x-for="eq in equipmentItems" :key="eq.key">
                    <div class="col-6 col-md-3">
                      <label class="card card-soft p-2 m-0 small text-center" style="cursor:pointer;"
                             :class="{'border-2 bg-cream': form.equipment.items.includes(eq.key)}">
                        <input type="checkbox" class="form-check-input visually-hidden"
                               :value="eq.key" :checked="form.equipment.items.includes(eq.key)"
                               @change="toggleEquipment(eq.key, $event.target.checked)">
                        <div class="display-6" x-text="eq.icon"></div>
                        <span x-text="eq.label"></span>
                      </label>
                    </div>
                  </template>
                </div>
              </div>

              <!-- ════════════ S8: CUMARTESİ ════════════ -->
              <div x-show="step === 8">
                <h3 class="h5 mb-1">Cumartesi çalışma</h3>
                <p class="text-secondary small mb-3">Aylık iş günü sayısını etkiler (22 / 24 / 26).</p>
                <div class="row g-3">
                  <template x-for="opt in saturdayOptions" :key="opt.key">
                    <div class="col-md-4">
                      <label class="card card-soft p-3 m-0 h-100 text-center" style="cursor:pointer;"
                             :class="{'border-2 bg-cream': form.saturday === opt.key}">
                        <input type="radio" class="form-check-input visually-hidden" name="saturday"
                               :value="opt.key" x-model="form.saturday">
                        <div class="display-5" x-text="opt.icon"></div>
                        <strong x-text="opt.label"></strong>
                        <div class="small text-secondary" x-text="opt.desc"></div>
                      </label>
                    </div>
                  </template>
                </div>
              </div>

              <!-- ════════════ S9: NOTLAR + İLETİŞİM + KVKK ════════════ -->
              <div x-show="step === 9">
                <h3 class="h5 mb-1">Notlar ve iletişim</h3>
                <p class="text-secondary small mb-3">Özel istekler için etiketleri seçin veya serbest yazın.</p>

                <!-- Etiket chip'leri -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                  <template x-for="tag in availableTags" :key="tag.key">
                    <button type="button" class="btn btn-sm" :class="form.notes.tags.includes(tag.key) ? 'btn-brand' : 'btn-outline-secondary'"
                            @click="toggleTag(tag.key)" x-text="`${tag.icon} ${tag.label}`"></button>
                  </template>
                </div>

                <textarea class="form-control" rows="2" maxlength="1000" x-model="form.notes.text"
                          placeholder="Diğer istekleriniz (alerji, özel diyet, sertifika, vb.)"></textarea>
                <div class="small text-secondary text-end mt-1" :class="{'text-danger': form.notes.text.length > 950}">
                  <span x-text="form.notes.text.length"></span> / 1000
                </div>

                <hr>

                <h4 class="h6 mb-3"><i class="bi bi-envelope text-brand me-1"></i>Size nasıl ulaşalım?</h4>
                <div class="row g-2">
                  <div class="col-md-6">
                    <label class="form-label small">Ad Soyad (opsiyonel)</label>
                    <input type="text" class="form-control" x-model="form.contact_name" placeholder="Ad Soyad">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small">Firma adı (opsiyonel)</label>
                    <input type="text" class="form-control" x-model="form.company_name" placeholder="ABC Ltd.">
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
                </div>
                <div class="text-danger small mt-2" x-show="errors.contact" x-text="errors.contact"></div>

                <div class="form-check mt-3">
                  <input class="form-check-input" type="checkbox" id="kvkk_quote" x-model="form.kvkk" required>
                  <label class="form-check-label small" for="kvkk_quote">
                    <a href="/yasal/aydinlatma-metni" target="_blank" class="text-brand text-decoration-none">Aydınlatma metnini</a> okudum,
                    iletişim bilgilerimin onaylı yemekçilerle paylaşılmasını kabul ediyorum.
                  </label>
                </div>
                <div class="text-danger small mt-1" x-show="errors.kvkk" x-text="errors.kvkk"></div>
              </div>

              <!-- Genel hata -->
              <div class="text-danger small mt-3" x-show="formError" x-text="formError"></div>

              <!-- Navigasyon -->
              <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-light border" @click="prev()" x-show="step > 1">
                  <i class="bi bi-arrow-left me-1"></i>Geri
                </button>
                <div></div>
                <button type="submit" class="btn btn-brand" :disabled="loading">
                  <template x-if="step < 9"><span>İleri <i class="bi bi-arrow-right ms-1"></i></span></template>
                  <template x-if="step === 9 && !loading"><span><i class="bi bi-send-fill me-1"></i>Talebi Gönder</span></template>
                  <template x-if="loading"><span><span class="spinner-border spinner-border-sm me-2"></span>Gönderiliyor…</span></template>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- ════════════ BAŞARI EKRANI ════════════ -->
        <div class="card card-soft" x-show="submitted" x-cloak>
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
            <a href="/" class="btn btn-brand mt-3"><i class="bi bi-house me-1"></i>Anasayfaya dön</a>
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
    step: 1,
    steps: ['Kişi sayısı', 'Öğünler', 'Menü', 'Segment', 'Lokasyon', 'Personel', 'Ekipman', 'Cumartesi', 'İletişim'],

    mealKeys: ['ogle', 'aksam', 'kumanya'],
    mealLabels: { ogle: '🍲 Öğle', aksam: '🌙 Akşam', kumanya: '🥪 Kumanya' },

    fixedItems: [
      { key: 'soup',      label: '🍜 Çorba'  },
      { key: 'main_dish', label: '🍖 Ana yemek' },
      { key: 'side_dish', label: '🍚 Yardımcı (pilav/makarna)' },
      { key: 'bread',     label: '🍞 Ekmek'  },
    ],

    segments: [
      { key: 'ekonomik', icon: '📦', label: 'Ekonomik', desc: 'En uygun fiyat, sade menü' },
      { key: 'genel',    icon: '⭐', label: 'Genel',    desc: 'Standart kalite, çeşitli menü' },
      { key: 'premium',  icon: '👑', label: 'Premium',  desc: 'Yüksek kalite, geniş seçenek' },
    ],

    equipmentItems: [
      { key: 'ocak',        icon: '🔥', label: 'Ocak / Set' },
      { key: 'firin',       icon: '🧯', label: 'Sanayi fırın' },
      { key: 'bulasik',     icon: '🧽', label: 'Endüstriyel bulaşık' },
      { key: 'sogutucu',    icon: '❄️', label: 'Soğutucu dolap' },
      { key: 'depo',        icon: '📦', label: 'Depo / Kiler' },
      { key: 'davlumbaz',   icon: '💨', label: 'Davlumbaz' },
      { key: 'salon',       icon: '🪑', label: 'Yemekhane salon' },
      { key: 'self_servis', icon: '🍲', label: 'Self-servis hat' },
    ],

    saturdayOptions: [
      { key: 'no',      icon: '🚫', label: 'Hayır',         desc: '22 iş günü/ay' },
      { key: 'partial', icon: '⏰', label: 'Mesaimizde',    desc: '24 iş günü/ay' },
      { key: 'yes',     icon: '✅', label: 'Evet',          desc: '26 iş günü/ay' },
    ],

    availableTags: [
      { key: 'helal',     icon: '🟢', label: 'Helal sertifikalı' },
      { key: 'vegan',     icon: '🌱', label: 'Vegan' },
      { key: 'glutensiz', icon: '🌾', label: 'Glutensiz' },
      { key: 'fistik',    icon: '🥜', label: 'Fıstık alerjisi' },
      { key: 'denizyok',  icon: '🐟', label: 'Deniz ürünü yok' },
      { key: 'acisiz',    icon: '🌶️', label: 'Acısız' },
      { key: 'ramazan',   icon: '🕌', label: 'Ramazan menüsü' },
      { key: 'ogrenci',   icon: '🎓', label: 'Öğrenci' },
      { key: 'agir_is',   icon: '👷', label: 'Ağır iş' },
      { key: 'tabakvar',  icon: '🍽️', label: 'Tabaklarımız var' },
      { key: 'tek_kul',   icon: '📦', label: 'Tek kullanımlık' },
      { key: 'soguk',     icon: '🧊', label: 'Soğuk zincir' },
    ],

    form: {
      guest_count: 100,
      meals: { ogle: 100, aksam: 0, kumanya: 0 },
      menu: {
        soup: true, main_dish: true, side_dish: true, bread: true,
        salad_bar_count: 2, dessert: 'fruit', drinks: 'rotation',
      },
      segment: 'genel',
      location: { city: '', district: '', address: '' },
      personnel: { enabled: false, cooks: 1, service: 2, cleaning: 1 },
      equipment: { enabled: false, items: [] },
      saturday: 'no',
      notes: { tags: [], text: '' },
      contact_name: '', company_name: '', contact_email: '', contact_phone: '',
      kvkk: false,
    },
    _ogleManual: false,

    errors: {},
    formError: null,
    loading: false,
    submitted: false,
    reference: '',
    successMessage: '',
    nextSteps: [],

    get totalMeals() {
      return (this.form.meals.ogle || 0) + (this.form.meals.aksam || 0) + (this.form.meals.kumanya || 0);
    },

    syncOgleFromGuest() {
      // Kişi sayısı değişti, öğle elle değişmediyse senkronize et
      if (!this._ogleManual) {
        this.form.meals.ogle = this.form.guest_count;
      }
    },
    recalcGuestFromMeals() {
      this._ogleManual = true;
      this.form.guest_count = this.totalMeals;
    },
    toggleMeal(key, on) {
      if (on && this.form.meals[key] === 0) {
        this.form.meals[key] = key === 'ogle' ? this.form.guest_count : 0;
      }
      if (!on) this.form.meals[key] = 0;
      this.recalcGuestFromMeals();
    },
    toggleEquipment(key, on) {
      const list = this.form.equipment.items;
      if (on && !list.includes(key)) list.push(key);
      if (!on) this.form.equipment.items = list.filter(k => k !== key);
    },
    toggleTag(key) {
      const list = this.form.notes.tags;
      if (list.includes(key)) {
        this.form.notes.tags = list.filter(k => k !== key);
      } else {
        list.push(key);
      }
    },

    // Menü segment'i çeşit sayısına göre
    menuSegment() {
      let count = ['soup','main_dish','side_dish','bread']
        .filter(k => this.form.menu[k]).length
        + (this.form.menu.salad_bar_count || 0)
        + (this.form.menu.dessert !== 'none' ? 1 : 0)
        + (this.form.menu.drinks !== 'none' ? 1 : 0);
      if (count <= 3) return 'Sade';
      if (count <= 5) return 'Ekonomik';
      if (count <= 7) return 'Standart';
      if (count <= 9) return 'Zengin';
      return 'Premium';
    },
    segmentColor() {
      const seg = this.menuSegment();
      return { Sade: 'secondary', Ekonomik: 'info', Standart: 'success', Zengin: 'warning', Premium: 'danger' }[seg] || 'secondary';
    },
    get menuPreview() {
      const out = [];
      if (this.form.menu.soup)      out.push('Çorba');
      if (this.form.menu.main_dish) out.push('Ana yemek');
      if (this.form.menu.side_dish) out.push('Pilav/makarna');
      if (this.form.menu.bread)     out.push('Ekmek');
      if (this.form.menu.salad_bar_count > 0) out.push(`${this.form.menu.salad_bar_count} salata`);
      if (this.form.menu.dessert === 'fruit')   out.push('Meyve');
      if (this.form.menu.dessert === 'dessert') out.push('Tatlı');
      if (this.form.menu.drinks === 'rotation') out.push('Ayran/yoğurt');
      if (this.form.menu.drinks === 'ayran')    out.push('Ayran');
      if (this.form.menu.drinks === 'yogurt')   out.push('Yoğurt');
      if (this.form.menu.drinks === 'both')     out.push('Ayran+yoğurt');
      return out;
    },

    next() {
      this.errors = {};
      if (this.step === 1) {
        if (!this.form.guest_count || this.form.guest_count < 1 || this.form.guest_count > 10000) {
          this.errors.guest_count = 'Kişi sayısı 1-10.000 arasında olmalı.';
          return;
        }
      } else if (this.step === 2) {
        if (this.totalMeals === 0) {
          this.errors.meals = 'En az bir öğün için kişi sayısı girin.';
          return;
        }
      } else if (this.step === 5) {
        if (!this.form.location.city || this.form.location.city.length < 2) {
          this.errors.city = 'Şehir gerekli.';
          return;
        }
      }

      if (this.step < 9) {
        this.step++;
      } else {
        this.submit();
      }
    },

    prev() {
      this.errors = {};
      if (this.step > 1) this.step--;
    },

    async submit() {
      // S9 doğrulamaları
      this.errors = {};
      if (!this.form.contact_email && !this.form.contact_phone) {
        this.errors.contact = 'E-posta veya telefon zorunlu.';
        return;
      }
      if (!this.form.kvkk) {
        this.errors.kvkk = 'KVKK onayı zorunlu.';
        return;
      }

      this.loading = true;
      this.formError = null;
      try {
        const fd = new FormData();
        fd.set('_csrf', this.csrf);
        fd.set('guest_count', this.form.guest_count);
        for (const [k, v] of Object.entries(this.form.meals))     fd.set(`meals[${k}]`, v);
        for (const [k, v] of Object.entries(this.form.menu))      fd.set(`menu[${k}]`, typeof v === 'boolean' ? (v ? '1' : '') : v);
        fd.set('segment', this.form.segment);
        fd.set('location[city]',     this.form.location.city);
        fd.set('location[district]', this.form.location.district);
        fd.set('location[address]',  this.form.location.address);
        fd.set('personnel[enabled]', this.form.personnel.enabled ? '1' : '');
        if (this.form.personnel.enabled) {
          fd.set('personnel[cooks]',    this.form.personnel.cooks    || 0);
          fd.set('personnel[service]',  this.form.personnel.service  || 0);
          fd.set('personnel[cleaning]', this.form.personnel.cleaning || 0);
        }
        fd.set('equipment[enabled]', this.form.equipment.enabled ? '1' : '');
        if (this.form.equipment.enabled) {
          this.form.equipment.items.forEach(item => fd.append('equipment[items][]', item));
        }
        fd.set('saturday', this.form.saturday);
        this.form.notes.tags.forEach(tag => fd.append('notes[tags][]', tag));
        fd.set('notes[text]', this.form.notes.text);
        fd.set('contact_name',  this.form.contact_name);
        fd.set('contact_email', this.form.contact_email);
        fd.set('contact_phone', this.form.contact_phone);
        fd.set('kvkk', '1');

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

<?php /** @var bool $authed @var ?array $user */ ?>

<!-- ════════════════════════════════════════════════
     HERO
     ════════════════════════════════════════════════ -->
<section class="py-5 py-lg-6" style="background: linear-gradient(140deg,#FAF6F0 0%,#F2E8D5 60%,#E8D9B8 100%);">
  <div class="container">
    <div class="row align-items-center gy-4">
      <div class="col-lg-7">
        <span class="badge badge-soft mb-3"><i class="bi bi-stars me-1"></i>Türkiye'nin Tarafsız Catering Pazaryeri</span>
        <h1 class="display-4 mb-3">Yemekhanenizin <span class="text-brand">Fiyatını Hesaplayın</span></h1>
        <p class="lead text-secondary">İhtiyaçlarınızı belirtin, bölgenizdeki yemekçilerin ortalama fiyatlarını anında görün. 9 soru, 30 saniye, KVKK uyumlu.</p>
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
            <li class="d-flex mb-3"><i class="bi bi-check-circle-fill text-brand me-2 mt-1"></i><div><strong>Maliyet tabanlı şeffaflık</strong><div class="text-secondary">Yemekçi gerçek maliyetini girer, müşteri bölgesinin ortalamasını görür.</div></div></li>
            <li class="d-flex mb-3"><i class="bi bi-check-circle-fill text-brand me-2 mt-1"></i><div><strong>Anonim yemekçi listesi</strong><div class="text-secondary">Firma isimleri gizli, fiyatlar açık. Detayları mail/telefon ile alırsınız.</div></div></li>
            <li class="d-flex"><i class="bi bi-check-circle-fill text-brand me-2 mt-1"></i><div><strong>KYC + sertifika doğrulama</strong><div class="text-secondary">Tüm yemekçiler vergi, sağlık ve hijyen denetiminden geçer.</div></div></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════════════════════
     3 KART (özet)
     ════════════════════════════════════════════════ -->
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

<!-- ════════════════════════════════════════════════
     HIZLI TEKLİF — 9 SORU WIZARD
     ════════════════════════════════════════════════ -->
<section id="hizli-teklif" class="py-5 bg-white border-top" x-data="quickQuoteWizard()">
  <div class="container">

    <!-- WIZARD (sonuç gelmeden) -->
    <div x-show="!result" x-cloak>
      <!-- Adım göstergesi -->
      <div class="text-center text-secondary small mb-2" x-text="`Adım ${step} / 9`"></div>
      <div class="progress mx-auto mb-4" style="height: 8px; max-width: 1000px;">
        <div class="progress-bar bg-brand" :style="`width: ${(step / 9) * 100}%`"></div>
      </div>

      <form @submit.prevent="next()" novalidate class="row justify-content-center">
        <input type="hidden" name="_csrf" :value="csrf">
        <div class="col-lg-10">

          <!-- ═════════ S1: GÜNLÜK KAÇ ÖĞÜN? ═════════ -->
          <div x-show="step === 1">
            <h2 class="display-5 text-center mb-4" style="font-family:'Cormorant Garamond', serif;">Günlük kaç öğün yemeğe ihtiyacınız var?</h2>
            <div class="row g-3 justify-content-center">
              <template x-for="opt in mealCountOptions" :key="opt.value">
                <div class="col-md-4">
                  <label class="card card-soft h-100 m-0 text-center p-4" style="cursor:pointer;"
                         :class="{'border-brand border-2 shadow': form.meal_count === opt.value}">
                    <input type="radio" class="visually-hidden" name="meal_count" :value="opt.value" x-model.number="form.meal_count">
                    <div class="my-3" x-html="opt.icon"></div>
                    <h3 class="h5 mb-1" x-text="opt.title"></h3>
                    <div class="small text-secondary" x-text="opt.subtitle"></div>
                    <button type="button" class="btn w-100 mt-3"
                            :class="form.meal_count === opt.value ? 'btn-brand' : 'btn-outline-brand'"
                            @click.prevent="form.meal_count = opt.value; next()" x-text="form.meal_count === opt.value ? 'Seçildi ✓' : 'Seç'"></button>
                  </label>
                </div>
              </template>
            </div>
          </div>

          <!-- ═════════ S2: KAÇ KİŞİLİK? ═════════ -->
          <div x-show="step === 2">
            <h2 class="display-5 text-center mb-2" style="font-family:'Cormorant Garamond', serif;">Kaç kişilik yemek hizmeti?</h2>
            <p class="text-center text-secondary mb-4">Slider ile veya kutuya yazarak girin.</p>
            <div class="card card-soft p-4 mx-auto" style="max-width: 700px;">
              <div class="d-flex align-items-center gap-3 mb-2">
                <input type="range" class="form-range flex-fill" min="1" max="2000" step="1" x-model.number="form.guest_count">
                <input type="number" class="form-control mono" style="width:140px;font-size:1.2rem;" min="1" max="10000" x-model.number="form.guest_count">
              </div>
              <div class="text-center small text-brand mt-2">
                <strong x-text="form.guest_count"></strong> kişi × <strong x-text="form.meal_count"></strong> öğün/gün
              </div>
              <div class="text-danger small mt-2" x-show="errors.guest_count" x-text="errors.guest_count"></div>
            </div>
          </div>

          <!-- ═════════ S3: MENÜ YAPISI ═════════ -->
          <div x-show="step === 3">
            <h2 class="display-5 text-center mb-2" style="font-family:'Cormorant Garamond', serif;">Menüde ne olsun?</h2>
            <p class="text-center text-secondary mb-4">Çeşit sayısına göre segment otomatik belirlenir.</p>

            <div class="card card-soft p-4 mx-auto" style="max-width: 760px;">
              <!-- Canlı önizleme -->
              <div class="mb-3 p-2 rounded" style="background:#FAF6F0;">
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
              <strong class="d-block mb-2 small">Sabit bileşenler</strong>
              <div class="row g-2 mb-3">
                <template x-for="item in fixedItems" :key="item.key">
                  <div class="col-6 col-md-3">
                    <label class="card card-soft p-2 m-0 text-center small" style="cursor:pointer;"
                           :class="{'border-brand border-2 bg-cream': form.menu[item.key]}">
                      <input type="checkbox" class="visually-hidden" x-model="form.menu[item.key]">
                      <span class="display-6" x-text="item.icon"></span>
                      <span x-text="item.label"></span>
                    </label>
                  </div>
                </template>
              </div>

              <!-- Salata bar slider -->
              <div class="mb-3">
                <label class="form-label small d-flex justify-content-between">
                  <span><strong>🥗 Salata bar çeşit sayısı</strong></span>
                  <strong x-text="`${form.menu.salad_bar_count} çeşit`"></strong>
                </label>
                <input type="range" class="form-range" min="0" max="7" step="1" x-model.number="form.menu.salad_bar_count">
              </div>

              <!-- Tatlı/meyve dönüşümlü TOGGLE -->
              <div class="row g-2 mb-2">
                <div class="col-md-6">
                  <label class="form-label small"><strong>🍰 Tatlı / Meyve / Meşrubat dönüşümlü</strong></label>
                  <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="dessert_rot" x-model="form.menu.dessert_rotation" role="switch">
                    <label for="dessert_rot" class="form-check-label small">
                      <span x-text="form.menu.dessert_rotation ? 'Var (gün gün dönüşüm)' : 'Yok'"></span>
                    </label>
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="form-label small"><strong>🥛 Ayran / Yoğurt</strong></label>
                  <select class="form-select form-select-sm" x-model="form.menu.drinks">
                    <option value="rotation">Rotasyon (gün gün)</option>
                    <option value="ayran">Sadece ayran</option>
                    <option value="yogurt">Sadece yoğurt</option>
                    <option value="both">Birlikte (her gün ikisi)</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- ═════════ S4: HİZMET SEGMENTİ ═════════ -->
          <div x-show="step === 4">
            <h2 class="display-5 text-center mb-4" style="font-family:'Cormorant Garamond', serif;">Hizmet segmenti?</h2>
            <div class="row g-3 justify-content-center">
              <template x-for="seg in segments" :key="seg.key">
                <div class="col-md-4">
                  <label class="card card-soft h-100 m-0 p-4 text-center" style="cursor:pointer;"
                         :class="{'border-brand border-2 shadow': form.segment === seg.key}">
                    <input type="radio" class="visually-hidden" name="segment" :value="seg.key" x-model="form.segment">
                    <div class="display-3 mb-2" x-text="seg.icon"></div>
                    <h3 class="h5 mb-1" x-text="seg.label"></h3>
                    <div class="small text-secondary" x-text="seg.desc"></div>
                  </label>
                </div>
              </template>
            </div>
          </div>

          <!-- ═════════ S5: LOKASYON ═════════ -->
          <div x-show="step === 5">
            <h2 class="display-5 text-center mb-2" style="font-family:'Cormorant Garamond', serif;">Hizmet lokasyonunuz?</h2>
            <p class="text-center text-secondary mb-4">Yemekçinin hizmet bölgesi ile eşleştirilir.</p>
            <div class="card card-soft p-4 mx-auto" style="max-width: 700px;">
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label small">Şehir <span class="text-brand">*</span></label>
                  <input type="text" class="form-control form-control-lg" x-model="form.location.city" placeholder="İstanbul">
                </div>
                <div class="col-md-6">
                  <label class="form-label small">İlçe</label>
                  <input type="text" class="form-control form-control-lg" x-model="form.location.district" placeholder="Şişli">
                </div>
                <div class="col-12">
                  <label class="form-label small">Adres / mahalle (opsiyonel)</label>
                  <input type="text" class="form-control" x-model="form.location.address" placeholder="Mecidiyeköy, Büyükdere Cad. No:1">
                </div>
              </div>
              <div class="text-danger small mt-2" x-show="errors.city" x-text="errors.city"></div>
            </div>
          </div>

          <!-- ═════════ S6: PERSONEL — 2 BÜYÜK KART ═════════ -->
          <div x-show="step === 6">
            <h2 class="display-5 text-center mb-2" style="font-family:'Cormorant Garamond', serif;">Personel desteği gerekli mi?</h2>
            <p class="text-center text-secondary mb-4">Yemekçinin sahaya personel göndermesi gerekiyor mu?</p>
            <div class="row g-3 justify-content-center">
              <div class="col-md-5">
                <label class="card card-soft h-100 m-0 p-4 text-center" style="cursor:pointer;"
                       :class="{'border-brand border-2 shadow': !form.personnel.enabled}">
                  <input type="radio" class="visually-hidden" name="p_enabled" :value="false" x-model="form.personnel.enabled">
                  <div class="display-3 mb-2">🚫</div>
                  <h3 class="h5">Hayır, personele ihtiyacım yok</h3>
                  <div class="small text-secondary">Sadece yemek tedariki yeterli — kendi personelimle servis ediyorum.</div>
                </label>
              </div>
              <div class="col-md-5">
                <label class="card card-soft h-100 m-0 p-4 text-center" style="cursor:pointer;"
                       :class="{'border-brand border-2 shadow': form.personnel.enabled}">
                  <input type="radio" class="visually-hidden" name="p_enabled" :value="true" x-model="form.personnel.enabled">
                  <div class="display-3 mb-2">👨‍🍳</div>
                  <h3 class="h5">Evet, personel istiyorum</h3>
                  <div class="small text-secondary">Aşçı, servis ve/veya temizlik personeli yemekçi tarafından gelsin.</div>
                </label>
              </div>
            </div>

            <!-- Personel sayıları -->
            <div x-show="form.personnel.enabled" class="card card-soft p-4 mx-auto mt-3" style="max-width: 700px;">
              <strong class="d-block mb-2 small">Kaç kişi gerekli?</strong>
              <div class="row g-2">
                <div class="col-md-4">
                  <label class="form-label small">👨‍🍳 Aşçı</label>
                  <input type="number" class="form-control mono" min="0" max="20" x-model.number="form.personnel.cooks">
                </div>
                <div class="col-md-4">
                  <label class="form-label small">🍽️ Servis personeli</label>
                  <input type="number" class="form-control mono" min="0" max="50" x-model.number="form.personnel.service">
                </div>
                <div class="col-md-4">
                  <label class="form-label small">🧽 Bulaşık / temizlik</label>
                  <input type="number" class="form-control mono" min="0" max="20" x-model.number="form.personnel.cleaning">
                </div>
              </div>
            </div>
          </div>

          <!-- ═════════ S7: EKİPMAN — 2 TEXTAREA ═════════ -->
          <div x-show="step === 7">
            <h2 class="display-5 text-center mb-2" style="font-family:'Cormorant Garamond', serif;">Mutfak ekipmanı durumu</h2>
            <p class="text-center text-secondary mb-4">Mevcut ekipmanlarınızı ve yemekçiden talep ettiklerinizi yazın. Talep ekipman varsa fiyata yansır.</p>
            <div class="row g-3 justify-content-center">
              <div class="col-md-6">
                <label class="form-label">
                  <i class="bi bi-house-check text-success me-1"></i>
                  <strong>Mevcut ekipmanlarınız</strong>
                  <div class="small text-secondary">Sahanızda hâlihazırda kurulu/var olan ekipmanlar</div>
                </label>
                <textarea class="form-control" rows="6" maxlength="1000"
                          x-model="form.equipment.has_existing"
                          placeholder="Örn: Endüstriyel ocak (4 gözlü), bulaşık makinesi, soğutucu dolap (2 adet), depo, ısıtmalı tezgah..."></textarea>
                <div class="small text-secondary text-end mt-1">
                  <span x-text="form.equipment.has_existing.length"></span> / 1000
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">
                  <i class="bi bi-tools text-brand me-1"></i>
                  <strong>Yemekçiden talep ettiğiniz ekipmanlar</strong>
                  <div class="small text-secondary">Bu kısım dolu olursa fiyata 12-36 ay amortisman eklenir</div>
                </label>
                <textarea class="form-control" rows="6" maxlength="1000"
                          x-model="form.equipment.requested"
                          placeholder="Örn: Sanayi fırın, davlumbaz, self-servis hat, yemekhane masası 200 kişilik..."></textarea>
                <div class="small text-secondary text-end mt-1">
                  <span x-text="form.equipment.requested.length"></span> / 1000
                </div>
              </div>
            </div>
            <div class="alert alert-light border small mt-3" x-show="form.equipment.requested.trim()">
              <i class="bi bi-info-circle text-brand me-1"></i>
              Talep ettiğiniz ekipmanlar için fiyatlandırma yemekçinin alış fiyatı + 12-36 ay amortismanla belirlenir.
            </div>
          </div>

          <!-- ═════════ S8: CUMARTESİ ═════════ -->
          <div x-show="step === 8">
            <h2 class="display-5 text-center mb-2" style="font-family:'Cormorant Garamond', serif;">Cumartesi çalışma?</h2>
            <p class="text-center text-secondary mb-4">Aylık iş günü sayısını etkiler.</p>
            <div class="row g-3 justify-content-center">
              <template x-for="opt in saturdayOptions" :key="opt.key">
                <div class="col-md-4">
                  <label class="card card-soft h-100 m-0 p-4 text-center" style="cursor:pointer;"
                         :class="{'border-brand border-2 shadow': form.saturday === opt.key}">
                    <input type="radio" class="visually-hidden" name="saturday" :value="opt.key" x-model="form.saturday">
                    <div class="display-3 mb-2" x-text="opt.icon"></div>
                    <h3 class="h5" x-text="opt.label"></h3>
                    <div class="small text-secondary" x-text="opt.desc"></div>
                  </label>
                </div>
              </template>
            </div>
          </div>

          <!-- ═════════ S9: NOTLAR + FİYAT ÖĞREN ═════════ -->
          <div x-show="step === 9">
            <h2 class="display-5 text-center mb-2" style="font-family:'Cormorant Garamond', serif;">Son adım: notlar</h2>
            <p class="text-center text-secondary mb-4">Özel istekler varsa belirtin. Sonra ortalama fiyatınızı görelim.</p>

            <div class="card card-soft p-4 mx-auto" style="max-width: 820px;">
              <strong class="d-block mb-2 small">Hızlı etiketler</strong>
              <div class="d-flex flex-wrap gap-2 mb-3">
                <template x-for="tag in availableTags" :key="tag.key">
                  <button type="button" class="btn btn-sm" :class="form.notes.tags.includes(tag.key) ? 'btn-brand' : 'btn-outline-secondary'"
                          @click="toggleTag(tag.key)" x-text="`${tag.icon} ${tag.label}`"></button>
                </template>
              </div>

              <textarea class="form-control" rows="3" maxlength="1000" x-model="form.notes.text"
                        placeholder="Diğer istekleriniz (alerji, özel diyet, sertifika, vb.)"></textarea>
              <div class="small text-secondary text-end mt-1">
                <span x-text="form.notes.text.length"></span> / 1000
              </div>
            </div>
          </div>

          <!-- Genel hata -->
          <div class="text-danger small mt-3" x-show="formError" x-text="formError"></div>

          <!-- ═════════ NAVİGASYON ═════════ -->
          <div class="d-flex justify-content-between mt-4 mx-auto" style="max-width: 820px;">
            <button type="button" class="btn btn-light border" @click="prev()" :disabled="step === 1">
              <i class="bi bi-arrow-left me-1"></i>Geri
            </button>
            <button type="submit" class="btn btn-brand btn-lg" :disabled="loading">
              <template x-if="step < 9"><span>Devam Et <i class="bi bi-arrow-right ms-1"></i></span></template>
              <template x-if="step === 9 && !loading"><span><i class="bi bi-magic me-1"></i>Fiyat Öğren</span></template>
              <template x-if="loading"><span><span class="spinner-border spinner-border-sm me-2"></span>Hesaplanıyor…</span></template>
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- ════════════════════════════════════════════════
         SONUÇ EKRANI: ortalama fiyat + anonim yemekçi listesi
         ════════════════════════════════════════════════ -->
    <div x-show="result" x-cloak>
      <div class="text-center mb-4">
        <span class="badge badge-soft mb-2"><i class="bi bi-check-circle-fill me-1"></i>Hazır</span>
        <h2 class="display-5">Bölgenizdeki ortalama fiyat</h2>
        <p class="text-secondary">Talep no: <code class="text-brand mono" x-text="result?.reference"></code></p>
      </div>

      <!-- ÜST: Fiyat özeti -->
      <div class="row g-3 mb-4 justify-content-center">
        <div class="col-md-4">
          <div class="card card-soft text-center p-4 h-100">
            <div class="small text-secondary">Kişi başı / öğün</div>
            <div class="display-4 text-brand mono" x-text="formatTl(result?.pricing?.per_person_per_meal)"></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-soft text-center p-4 h-100">
            <div class="small text-secondary">Aylık tahmini toplam</div>
            <div class="h3 text-brand mono mt-2" x-text="formatTl(result?.pricing?.monthly_total)"></div>
            <div class="small text-secondary mt-1">
              ~<span x-text="formatTl(result?.pricing?.monthly_total_min)"></span> – <span x-text="formatTl(result?.pricing?.monthly_total_max)"></span>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card card-soft text-center p-4 h-100">
            <div class="small text-secondary">Hesap detayı</div>
            <div class="small mt-2 text-start mx-auto" style="max-width:220px;">
              <div>👥 <span x-text="form.guest_count"></span> kişi</div>
              <div>🍽️ <span x-text="form.meal_count"></span> öğün/gün</div>
              <div>📅 <span x-text="result?.pricing?.business_days"></span> iş günü/ay</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ORTA: Anonim yemekçi listesi -->
      <div class="card card-soft mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h3 class="h5 mb-0"><i class="bi bi-incognito text-brand me-2"></i>Anonim Yemekçi Listesi</h3>
          <span class="small text-secondary">Firma isimleri gizli — detaylar iletişim sonrası açılır</span>
        </div>
        <div class="list-group list-group-flush">
          <template x-for="sup in result?.anonymous_suppliers || []" :key="sup.code">
            <div class="list-group-item p-3">
              <div class="row g-2 align-items-center">
                <div class="col-md-1 text-center">
                  <div class="rounded-circle bg-brand text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width:44px;height:44px;">
                    <span x-text="sup.code.slice(-1)"></span>
                  </div>
                </div>
                <div class="col-md-4">
                  <strong x-text="sup.code"></strong>
                  <div class="small text-secondary">
                    <i class="bi bi-geo-alt"></i> <span x-text="`${sup.city} / ${sup.district}`"></span>
                  </div>
                </div>
                <div class="col-md-3 small">
                  <i class="bi bi-star-fill text-warning"></i> <strong x-text="sup.rating"></strong>
                  · <span x-text="`${sup.years} yıl`"></span>
                  · <span x-text="`${sup.capacity.toLocaleString('tr-TR')} kapasite`"></span>
                </div>
                <div class="col-md-2 small">
                  <template x-for="cert in sup.certifications" :key="cert">
                    <span class="badge bg-success-subtle text-success-emphasis me-1" x-text="cert"></span>
                  </template>
                </div>
                <div class="col-md-2 text-end">
                  <div class="h4 mono mb-0 text-brand" x-text="formatTl(sup.price_per_meal)"></div>
                  <div class="small text-secondary">/ öğün</div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <!-- ALT: Detaylı teklif iletişim formu (opsiyonel) -->
      <div class="card card-soft mx-auto" style="max-width: 760px;" x-show="!contactSent">
        <div class="card-body p-4">
          <h3 class="h5"><i class="bi bi-envelope-paper text-brand me-2"></i>Detaylı teklif almak ister misiniz?</h3>
          <p class="text-secondary small">İletişim bilgilerinizi bırakın, onaylı yemekçilerden 24 saat içinde detaylı teklif gelir.</p>

          <form @submit.prevent="sendContact()" novalidate>
            <input type="hidden" :value="csrf">
            <div class="row g-2">
              <div class="col-md-6">
                <label class="form-label small">Ad Soyad (opsiyonel)</label>
                <input type="text" class="form-control" x-model="contact.name" placeholder="Ad Soyad">
              </div>
              <div class="col-md-6">
                <label class="form-label small">Firma adı (opsiyonel)</label>
                <input type="text" class="form-control" x-model="contact.company" placeholder="ABC Ltd.">
              </div>
              <div class="col-md-6">
                <label class="form-label small">E-posta</label>
                <input type="email" class="form-control" x-model="contact.email" placeholder="ornek@firma.com.tr">
                <div class="text-danger small mt-1" x-show="contactErrors.contact_email" x-text="contactErrors.contact_email"></div>
              </div>
              <div class="col-md-6">
                <label class="form-label small">Telefon</label>
                <input type="tel" class="form-control" x-model="contact.phone" placeholder="0532 123 45 67">
                <div class="text-danger small mt-1" x-show="contactErrors.contact_phone" x-text="contactErrors.contact_phone"></div>
              </div>
            </div>
            <div class="text-danger small mt-2" x-show="contactErrors.contact" x-text="contactErrors.contact"></div>

            <div class="form-check mt-3">
              <input class="form-check-input" type="checkbox" id="kvkk_contact" x-model="contact.kvkk" required>
              <label class="form-check-label small" for="kvkk_contact">
                <a href="/yasal/aydinlatma-metni" target="_blank" class="text-brand text-decoration-none">Aydınlatma metnini</a> okudum,
                iletişim bilgilerimin onaylı yemekçilerle paylaşılmasını kabul ediyorum.
              </label>
            </div>
            <div class="text-danger small mt-1" x-show="contactErrors.kvkk" x-text="contactErrors.kvkk"></div>

            <div class="text-danger small mt-2" x-show="contactFormError" x-text="contactFormError"></div>

            <button type="submit" class="btn btn-brand btn-lg w-100 mt-3" :disabled="contactLoading">
              <template x-if="!contactLoading"><span><i class="bi bi-send-fill me-1"></i>Detaylı teklif al</span></template>
              <template x-if="contactLoading"><span><span class="spinner-border spinner-border-sm me-2"></span>Gönderiliyor…</span></template>
            </button>
          </form>
        </div>
      </div>

      <!-- İletişim bırakıldı ekranı -->
      <div class="card card-soft mx-auto text-center p-4" style="max-width: 760px;" x-show="contactSent" x-cloak>
        <i class="bi bi-check-circle-fill display-1 text-success"></i>
        <h3 class="display-6 my-3">Teklif talebiniz alındı!</h3>
        <p class="text-secondary" x-text="contactMessage"></p>
        <a href="/" class="btn btn-outline-brand mt-2"><i class="bi bi-house me-1"></i>Anasayfaya dön</a>
      </div>

      <!-- Yeniden başlat -->
      <div class="text-center mt-4">
        <button type="button" class="btn btn-link text-secondary" @click="restart()">
          <i class="bi bi-arrow-counterclockwise me-1"></i>Yeni hesaplama yap
        </button>
      </div>
    </div>

  </div>
</section>

<script>
function quickQuoteWizard() {
  return {
    csrf: '<?= e(csrf_token()) ?>',
    step: 1,

    mealCountOptions: [
      { value: 1, title: 'Tek Öğün', subtitle: 'Sadece öğle yemeği', icon: '<i class="bi bi-cup-hot display-1 text-brand"></i>' },
      { value: 2, title: 'Çift Öğün', subtitle: 'Öğle + akşam',       icon: '<i class="bi bi-cup-hot display-1 text-brand"></i><i class="bi bi-cup-hot display-1 text-brand opacity-75"></i>' },
      { value: 3, title: 'Üç Öğün',   subtitle: 'Sabah + öğle + akşam', icon: '<i class="bi bi-cup-hot display-1 text-brand"></i><i class="bi bi-cup-hot display-1 text-brand opacity-75"></i><i class="bi bi-cup-hot display-1 text-brand opacity-50"></i>' },
    ],

    fixedItems: [
      { key: 'soup',      label: 'Çorba', icon: '🍜' },
      { key: 'main_dish', label: 'Ana yemek', icon: '🍖' },
      { key: 'side_dish', label: 'Pilav/makarna', icon: '🍚' },
      { key: 'bread',     label: 'Ekmek', icon: '🍞' },
    ],

    segments: [
      { key: 'ekonomik', icon: '📦', label: 'Ekonomik', desc: 'En uygun fiyat, sade menü' },
      { key: 'genel',    icon: '⭐', label: 'Genel',    desc: 'Standart kalite, çeşitli menü' },
      { key: 'premium',  icon: '👑', label: 'Premium',  desc: 'Yüksek kalite, geniş seçenek' },
    ],

    saturdayOptions: [
      { key: 'no',      icon: '🚫', label: 'Hayır',      desc: '22 iş günü/ay' },
      { key: 'partial', icon: '⏰', label: 'Mesaimizde', desc: '24 iş günü/ay' },
      { key: 'yes',     icon: '✅', label: 'Evet',       desc: '26 iş günü/ay' },
    ],

    availableTags: [
      { key: 'helal',     icon: '🟢', label: 'Helal' },
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
      meal_count: 1,
      guest_count: 100,
      menu: {
        soup: true, main_dish: true, side_dish: true, bread: true,
        salad_bar_count: 2, dessert_rotation: false, drinks: 'rotation',
      },
      segment: 'genel',
      location: { city: '', district: '', address: '' },
      personnel: { enabled: false, cooks: 1, service: 2, cleaning: 1 },
      equipment: { has_existing: '', requested: '' },
      saturday: 'no',
      notes: { tags: [], text: '' },
    },

    errors: {},
    formError: null,
    loading: false,
    result: null,

    contact: { name: '', company: '', email: '', phone: '', kvkk: false },
    contactErrors: {},
    contactFormError: null,
    contactLoading: false,
    contactSent: false,
    contactMessage: '',

    toggleTag(key) {
      const list = this.form.notes.tags;
      if (list.includes(key)) this.form.notes.tags = list.filter(k => k !== key);
      else list.push(key);
    },

    menuSegment() {
      let count = ['soup', 'main_dish', 'side_dish', 'bread'].filter(k => this.form.menu[k]).length
        + (this.form.menu.salad_bar_count || 0)
        + (this.form.menu.dessert_rotation ? 1 : 0)
        + (this.form.menu.drinks !== 'none' ? 1 : 0);
      if (count <= 3) return 'Sade';
      if (count <= 5) return 'Ekonomik';
      if (count <= 7) return 'Standart';
      if (count <= 9) return 'Zengin';
      return 'Premium';
    },
    segmentColor() {
      return { Sade: 'secondary', Ekonomik: 'info', Standart: 'success', Zengin: 'warning', Premium: 'danger' }[this.menuSegment()] || 'secondary';
    },
    get menuPreview() {
      const out = [];
      if (this.form.menu.soup)      out.push('Çorba');
      if (this.form.menu.main_dish) out.push('Ana yemek');
      if (this.form.menu.side_dish) out.push('Pilav/makarna');
      if (this.form.menu.bread)     out.push('Ekmek');
      if (this.form.menu.salad_bar_count > 0) out.push(`${this.form.menu.salad_bar_count} salata`);
      if (this.form.menu.dessert_rotation) out.push('Tatlı/meyve dönüşüm');
      if (this.form.menu.drinks === 'rotation') out.push('Ayran/yoğurt rotasyon');
      if (this.form.menu.drinks === 'ayran')    out.push('Ayran');
      if (this.form.menu.drinks === 'yogurt')   out.push('Yoğurt');
      if (this.form.menu.drinks === 'both')     out.push('Ayran+yoğurt');
      return out;
    },

    next() {
      this.errors = {};
      if (this.step === 1) {
        if (![1, 2, 3].includes(this.form.meal_count)) return;
      } else if (this.step === 2) {
        if (!this.form.guest_count || this.form.guest_count < 1 || this.form.guest_count > 10000) {
          this.errors.guest_count = 'Kişi sayısı 1-10.000 arasında olmalı.';
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
        this.scrollToTop();
      } else {
        this.submit();
      }
    },

    prev() {
      this.errors = {};
      if (this.step > 1) {
        this.step--;
        this.scrollToTop();
      }
    },

    scrollToTop() {
      this.$nextTick(() => {
        const el = document.getElementById('hizli-teklif');
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    },

    async submit() {
      this.loading = true;
      this.formError = null;
      try {
        const fd = new FormData();
        fd.set('_csrf', this.csrf);
        fd.set('meal_count',  this.form.meal_count);
        fd.set('guest_count', this.form.guest_count);
        for (const [k, v] of Object.entries(this.form.menu)) {
          fd.set(`menu[${k}]`, typeof v === 'boolean' ? (v ? '1' : '') : v);
        }
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
        fd.set('equipment[has_existing]', this.form.equipment.has_existing);
        fd.set('equipment[requested]',    this.form.equipment.requested);
        fd.set('saturday', this.form.saturday);
        this.form.notes.tags.forEach(tag => fd.append('notes[tags][]', tag));
        fd.set('notes[text]', this.form.notes.text);

        const res = await fetch('/api/v1/hizli-teklif', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) {
          this.formError = data.message || 'Form gönderilemedi.';
          if (data.errors) for (const [k, msgs] of Object.entries(data.errors)) this.errors[k] = msgs[0];
        } else {
          this.result = data.data;
          this.scrollToTop();
        }
      } catch (e) {
        this.formError = 'Sunucuya ulaşılamadı: ' + e.message;
      } finally {
        this.loading = false;
      }
    },

    async sendContact() {
      this.contactErrors = {};
      this.contactFormError = null;
      if (!this.contact.email && !this.contact.phone) {
        this.contactErrors.contact = 'E-posta veya telefon zorunlu.';
        return;
      }
      if (!this.contact.kvkk) {
        this.contactErrors.kvkk = 'KVKK onayı zorunlu.';
        return;
      }
      this.contactLoading = true;
      try {
        const fd = new FormData();
        fd.set('_csrf', this.csrf);
        fd.set('reference', this.result.reference);
        fd.set('contact_name',  this.contact.name);
        fd.set('company_name',  this.contact.company);
        fd.set('contact_email', this.contact.email);
        fd.set('contact_phone', this.contact.phone);
        fd.set('kvkk', '1');

        const res = await fetch('/api/v1/hizli-teklif/iletisim', { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) {
          this.contactFormError = data.message || 'Gönderilemedi.';
          if (data.errors) for (const [k, msgs] of Object.entries(data.errors)) this.contactErrors[k] = msgs[0];
        } else {
          this.contactSent = true;
          this.contactMessage = data.data.message;
        }
      } catch (e) {
        this.contactFormError = 'Sunucuya ulaşılamadı: ' + e.message;
      } finally {
        this.contactLoading = false;
      }
    },

    restart() {
      this.step = 1;
      this.result = null;
      this.contactSent = false;
      this.contact = { name: '', company: '', email: '', phone: '', kvkk: false };
      this.contactErrors = {};
      this.scrollToTop();
    },

    formatTl(v) {
      if (v === null || v === undefined) return '— ₺';
      return Number(v).toLocaleString('tr-TR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' ₺';
    },
  };
}
</script>

<?php
/**
 * Çerez izni banner — opt-in (KVKK + ePrivacy uyumlu).
 * Tercihler localStorage'da `yh_consent` anahtarında saklanır:
 *   { decision:'accept_all'|'reject_all'|'custom', timestamp, functional, analytics, marketing }
 * Zorunlu çerezler her durumda çalışır (session, csrf).
 */
?>
<div x-data="cookieConsent()" x-init="init()" x-cloak>
  <!-- Ana banner -->
  <div class="position-fixed bottom-0 start-0 end-0 p-3 p-md-4"
       style="z-index: 1080; pointer-events: none;"
       x-show="visible" x-transition.opacity>
    <div class="card card-soft mx-auto shadow-lg" style="max-width: 720px; pointer-events: auto;">
      <div class="card-body">
        <div class="d-flex align-items-start">
          <i class="bi bi-cookie text-accent fs-3 me-3 d-none d-md-inline"></i>
          <div class="flex-fill">
            <strong class="d-block mb-1">Çerez kullanıyoruz 🍪</strong>
            <p class="small text-secondary mb-2">
              Yemekhaneci'nin temel işlevleri için zorunlu çerezler kullanılır.
              İsteğe bağlı çerezleri (işlevsel, analitik) kabul ederek deneyiminizi iyileştirmemize yardım edebilirsiniz.
              Ayrıntı: <a href="/yasal/cerez-politikasi" class="text-brand text-decoration-none">Çerez Politikası</a> ·
              <a href="/yasal/aydinlatma-metni" class="text-brand text-decoration-none">KVKK</a>
            </p>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-brand btn-sm" @click="acceptAll()">
                <i class="bi bi-check2 me-1"></i>Tümünü kabul et
              </button>
              <button type="button" class="btn btn-light border btn-sm" @click="rejectAll()">
                Sadece zorunlu
              </button>
              <button type="button" class="btn btn-link btn-sm text-secondary text-decoration-none" @click="showCustom = !showCustom">
                Tercihimi ayarla
              </button>
            </div>

            <!-- Detaylı tercihler -->
            <div x-show="showCustom" x-transition class="mt-3 border-top pt-3">
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="cookie-essential" checked disabled>
                <label class="form-check-label small" for="cookie-essential">
                  <strong>Zorunlu</strong> · Oturum, CSRF güvenliği · <em>her zaman aktif</em>
                </label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="cookie-functional" x-model="prefs.functional">
                <label class="form-check-label small" for="cookie-functional">
                  <strong>İşlevsel</strong> · Tema, dil tercihi
                </label>
              </div>
              <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="cookie-analytics" x-model="prefs.analytics">
                <label class="form-check-label small" for="cookie-analytics">
                  <strong>Analitik</strong> · Plausible (KVKK uyumlu, IP anonim) — <em>henüz aktif değil</em>
                </label>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="cookie-marketing" x-model="prefs.marketing" disabled>
                <label class="form-check-label small text-secondary" for="cookie-marketing">
                  <strong>Pazarlama</strong> · <em>kullanılmıyor</em>
                </label>
              </div>
              <button type="button" class="btn btn-brand btn-sm" @click="saveCustom()">
                <i class="bi bi-floppy me-1"></i>Tercihimi kaydet
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function cookieConsent() {
  return {
    visible: false,
    showCustom: false,
    prefs: { functional: true, analytics: false, marketing: false },

    init() {
      try {
        const raw = localStorage.getItem('yh_consent');
        if (!raw) {
          this.visible = true;
          return;
        }
        const v = JSON.parse(raw);
        // 1 yıldan eski kayıt varsa yeniden sor
        if (v.timestamp && (Date.now() - v.timestamp) > 365 * 24 * 60 * 60 * 1000) {
          this.visible = true;
        } else {
          this.applyConsent(v);
        }
      } catch (e) {
        this.visible = true;
      }

      // Footer linkinden tekrar açma
      document.addEventListener('yh:open-cookie-prefs', () => {
        this.showCustom = true;
        this.visible = true;
      });
    },

    acceptAll() {
      this.persist('accept_all', { functional: true, analytics: true, marketing: false });
    },

    rejectAll() {
      this.persist('reject_all', { functional: false, analytics: false, marketing: false });
    },

    saveCustom() {
      this.persist('custom', { ...this.prefs });
    },

    persist(decision, prefs) {
      const record = { decision, timestamp: Date.now(), version: 1, ...prefs };
      try {
        localStorage.setItem('yh_consent', JSON.stringify(record));
      } catch (e) { /* localStorage disabled */ }
      this.visible = false;
      this.applyConsent(record);
    },

    applyConsent(consent) {
      // Analytics ileride eklendiğinde burada init edilir
      // Theme'i yükleme dışı functional cookie'ler işlevsel onayına göre çalışır
      window.__yhConsent = consent;
    },
  };
}
</script>

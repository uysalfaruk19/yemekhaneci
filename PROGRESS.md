# Yemekhaneci.com.tr — Geliştirme İlerlemesi

> Bu dosya **her faz sonunda güncellenir**. Durum işaretleri: ☐ başlanmadı · ◐ devam ediyor · ☑ tamamlandı.

**Son güncelleme:** 2026-05-08
**Aktif faz:** Faz 0 → Faz 1 geçişi
**Repo:** [uysalfaruk19/yemekhaneci](https://github.com/uysalfaruk19/yemekhaneci)

---

## Genel Bakış

| Faz | Başlık | Süre | Durum | Başlangıç | Bitiş |
|-----|--------|------|:-----:|-----------|-------|
| Faz 0 | Proje Başlatma | 1 hafta | ◐ | 2026-05-08 | — |
| Faz 1 | Temel (DB + Auth + KYC) | 3 hafta | ☐ | — | — |
| Faz 2 | Yemekçi Maliyet Paneli | 3 hafta | ☐ | — | — |
| Faz 3 | Anasayfa Wizard + Fiyat Motoru | 3 hafta | ☐ | — | — |
| Faz 4 | Mail / Lead Capture / KVKK | 2 hafta | ☐ | — | — |
| Faz 5 | Menü Kataloğu | 2 hafta | ☐ | — | — |
| Faz 6 | Sipariş + Ödeme | 3 hafta | ☐ | — | — |
| Faz 7 | Teklif + Mesajlaşma + Yorum | 2 hafta | ☐ | — | — |
| Faz 8 | Beta / Pilot | 3 hafta | ☐ | — | — |
| Faz 9 | Lansman + Büyüme | sürekli | ☐ | — | — |

---

## Faz 0 — Proje Başlatma

**Süre hedefi:** 1 hafta · **Sorumlu:** Geliştirme ekibi

### Alt Görevler

| # | Görev | Durum | Not |
|---|-------|:-----:|-----|
| 0.1 | Git repo başlatma (`uysalfaruk19/yemekhaneci`) | ☑ | `claude/start-yemekhaneci-project-xCJFu` |
| 0.2 | PRD 14.2 klasör iskeleti | ☑ | `.gitkeep` ile boş dizinler korundu |
| 0.3 | `.gitignore` (PHP + Node + .env + uploads + storage) | ☑ | |
| 0.4 | `.env.example` (DB, Redis, mail, SMS, ödeme, B2, KVKK) | ☑ | PRD 14.1 stack'inden derlendi |
| 0.5 | `README.md` (proje tanımı + kurulum + faz haritası) | ☑ | |
| 0.6 | `PROGRESS.md` (bu dosya) | ☑ | |
| 0.7 | `DECISIONS.md` (ADR-lite, 8 başlangıç kaydı) | ☑ | |
| 0.8 | `composer.json` (PHP bağımlılıkları taslağı, install YOK) | ☑ | |
| 0.9 | `package.json` (frontend build taslağı, install YOK) | ☑ | |
| 0.10 | Hostinger VPS satın alma + ilk SSH erişimi | ☐ | UYSA |
| 0.11 | DNS yönlendirme (yemekhaneci.com.tr → VPS) | ☐ | Cloudflare |
| 0.12 | Traefik + Let's Encrypt SSL kurulumu | ☐ | VPS üzerinde |
| 0.13 | `staging.yemekhaneci.com.tr` subdomain | ☐ | |
| 0.14 | iyzico merchant başvurusu | ☐ | UYSA, paralel |
| 0.15 | PayTR merchant başvurusu | ☐ | UYSA, paralel |
| 0.16 | PostMark hesap kurulumu + DKIM/SPF | ☐ | UYSA |
| 0.17 | Netgsm header onayı (`YEMEKHANECI`) | ☐ | UYSA |
| 0.18 | VERBİS başvurusu (KVKK) | ☐ | 30+ gün, UYSA |
| 0.19 | Sentry + UptimeRobot hesap | ☐ | |
| 0.20 | Backblaze B2 bucket + retention politikası | ☐ | |

### Kabul Kriterleri

- [ ] `git clone` sonrası `cp .env.example .env && composer install && npm install` komutları planlanan adımları kapsıyor.
- [ ] Boş klasör yok (`find . -type d -empty` → 0 satır).
- [ ] `.env` repo'da yok (sadece `.env.example`).
- [ ] Tüm Faz 0 dokümantasyon dosyaları kökte mevcut.
- [ ] Hostinger VPS, DNS, SSL ve staging hazır.

---

## Faz 1 — Temel (DB + Auth + KYC) — *Planlanıyor*

### Alt Görevler (Taslak)

- [ ] Çekirdek framework kararı (Laravel 11 vs saf PHP) → `DECISIONS.md` ADR-001 güncellemesi
- [ ] `composer install` + `npm install` ilk çalıştırma
- [ ] `database_schema.sql` → migration dosyalarına dönüştürme (`database/migrations/`)
- [ ] Seeder: kategoriler (8 başlangıç kategorisi)
- [ ] User modeli + 3 user_type (admin / supplier / customer)
- [ ] Kayıt + giriş + şifre sıfırlama (Argon2id, rate limit)
- [ ] CSRF middleware + session güvenliği (httponly, secure, samesite=lax)
- [ ] Admin için 2FA (Google Authenticator)
- [ ] Yemekçi KYC akışı (vergi levhası, imza sirküleri yükleme)
- [ ] Admin panel iskeleti (12 modül sidebar)
- [ ] Yemekçi panel iskeleti (6 sekme sidebar)
- [ ] Müşteri panel iskeleti
- [ ] Audit log altyapısı

### Kabul Kriterleri

- [ ] 3 user_type ile kayıt + giriş + çıkış çalışıyor.
- [ ] Yemekçi KYC dokümanları yüklenebiliyor; admin onaylayabiliyor.
- [ ] Tüm tablolar `utf8mb4_turkish_ci`.
- [ ] Migration `php artisan migrate:fresh` ile sıfırdan kurulabiliyor.
- [ ] Auth akışına unit + feature test yazıldı.

---

## Faz 2 — Yemekçi Maliyet Paneli — *Bekliyor*

### Alt Görevler (Taslak)

- [ ] 6 sekmeli maliyet matrisi UI (referans: `yemekci-maliyet-paneli.html`)
- [ ] Sekme 1: Ham madde maliyetleri
- [ ] Sekme 2: İşçilik
- [ ] Sekme 3: Mutfak/operasyon
- [ ] Sekme 4: Lojistik/teslimat
- [ ] Sekme 5: Genel giderler
- [ ] Sekme 6: Marj + nihai fiyat
- [ ] Canlı önizleme (Alpine.js, debounce'lı)
- [ ] Maliyet snapshot kayıt + sürüm geçmişi
- [ ] Müsaitlik takvimi

---

## Faz 3 — Anasayfa Wizard + Fiyat Motoru — *Bekliyor*

### Alt Görevler (Taslak)

- [ ] 9 soru wizard (referans: `yemekhaneci-fiyat-hesaplama.html`)
- [ ] Akıllı öğün dağılımı algoritması
- [ ] Fiyat motoru (yemekçi maliyetlerinden ortalama)
- [ ] Anonim yemekçi listesi (isimler maskelenmiş)
- [ ] Eşleştirme servisi (`SupplierMatcher`)
- [ ] Sonuç ekranı (fiyat aralığı + anonim liste + mail formu)

---

## Faz 4 — Mail / Lead Capture / KVKK — *Bekliyor*

- [ ] Lead capture formu (e-posta zorunlu)
- [ ] PostMark entegrasyonu + transactional template'ler
- [ ] KVKK aydınlatma metni + çerez banner (opt-in)
- [ ] `/hesabim/veri-silme` formu
- [ ] Veri ihlali bildirim akışı

---

## Faz 5 — Menü Kataloğu — *Bekliyor*

- [ ] Yemekçi menü oluşturma + düzenleme
- [ ] Menü şablonları (klasik, vegan, fit, lüks vb.)
- [ ] Görsel yükleme + WebP dönüşüm
- [ ] FULLTEXT arama (menü adı + açıklama)
- [ ] Müşteri tarama UI

---

## Faz 6 — Sipariş + Ödeme — *Bekliyor*

- [ ] Sepet
- [ ] iyzico kart ödeme + 3D Secure
- [ ] PayTR taksit/B2B
- [ ] Banka havalesi akışı
- [ ] Sipariş durum makinesi (pending → accepted → preparing → delivered)
- [ ] E-fatura entegrasyonu (ParamPos veya Foriba)
- [ ] Komisyon hesaplama + raporlama

---

## Faz 7 — Teklif + Mesajlaşma + Yorum — *Bekliyor*

- [ ] Reaktif teklif akışı (müşteri → yemekçi)
- [ ] Yemekçi-müşteri mesajlaşma
- [ ] Yorum/değerlendirme (5 yıldız + metin)
- [ ] Anlaşmazlık yönetimi (admin paneli)

---

## Faz 8 — Beta / Pilot — *Bekliyor*

- [ ] 30-40 pilot yemekçi onboarding
- [ ] Hata izleme (Sentry alarmları)
- [ ] Performans optimizasyonu (Lighthouse > 85)
- [ ] Yük testi
- [ ] Manuel smoke test checklist'i

---

## Faz 9 — Lansman + Büyüme — *Sürekli*

- [ ] Halka açık lansman duyurusu
- [ ] PR / sosyal medya kampanyası
- [ ] İçerik (blog, SEO)
- [ ] Yemekçi büyümesi (100+ hedef)
- [ ] GMV ₺3-5M aylık hedef

---

## Notlar

- Her faz bitiminde **staging deploy + manuel smoke test** zorunlu.
- Faz geçişlerinde `DECISIONS.md` güncellenmeli.
- Commit'ler Türkçe + Conventional Commits formatında (bkz. `CLAUDE.md`).

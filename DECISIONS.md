# Yemekhaneci.com.tr — Mimari Karar Kayıtları (ADR)

> Her büyük teknik karar burada **tarih, bağlam, karar, gerekçe, alternatif, etki** ile kayıt altına alınır.
> Format: ADR-lite. Yeni karar ekleyince numara artırılır, eski kararlar **silinmez** (status alanı ile güncellenir).

---

## ADR-001 — Backend dili: PHP 8.2

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi
- **Bağlam:** UYSA ekibi PHP'de deneyimli; mevcut Hostinger VPS'i PHP odaklı.
- **Karar:** Backend dili PHP 8.2 olacak. Framework seçimi (saf PHP / Laravel 11 / Symfony) **Faz 1 başında** ayrı ADR ile karar verilecek.
- **Gerekçe:** Ekip uzmanlığı; PHP 8.2'nin tip sistemi (readonly, enum, fibers) modern projeler için yeterli.
- **Alternatif:** Node.js + TypeScript (ekip uzmanlığı az), Python + Django (ekip uzmanlığı yok), Go (öğrenme eğrisi yüksek).
- **Etki:** `composer.json` yazıldı, `vendor/` Faz 1 başında doldurulacak. PHP 8.2+ docker imajı staging'e kurulacak.

## ADR-002 — Veritabanı: MySQL 8 + utf8mb4_turkish_ci

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi
- **Bağlam:** Türkçe karakterler ve sıralama (örn. "İ"/"ı" doğru sırasıyla); Hostinger VPS MySQL 8 ile geliyor.
- **Karar:** Tüm tablolar `utf8mb4` charset, `utf8mb4_turkish_ci` collation. PostgreSQL kullanılmayacak.
- **Gerekçe:** Türkçe sıralama doğru (`utf8mb4_unicode_ci` "İ"yi yanlış sıralar); MySQL 8'in JSON, FULLTEXT, SPATIAL özellikleri PRD'nin ihtiyaçlarına yeter.
- **Alternatif:** PostgreSQL (Türkçe collation eklentisi gerekli, ekip deneyimi az).
- **Etki:** `database_schema.sql` zaten bu collation ile yazıldı; `.env.example`'da `DB_COLLATION=utf8mb4_turkish_ci` sabit.

## ADR-003 — Frontend: Bootstrap 5 + Alpine.js (jQuery yasak)

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi
- **Bağlam:** Hızlı geliştirme + SEO + minimum JS bundle ihtiyacı; admin panelde React 19 isteniyor.
- **Karar:** Public + supplier + customer panellerinde Bootstrap 5 utility classes + Alpine.js. jQuery KESİNLİKLE kullanılmayacak. Admin panelde karmaşık UI için React 19 (sadece `/yonetim/*` rotalarında).
- **Gerekçe:** Bootstrap 5 hazır responsive grid + komponentler; Alpine.js light reactive (15KB) state için yeterli; React mass dashboard'da olgunlaşmış.
- **Alternatif:** Vue 3 (ekip uzmanlığı az), Tailwind + HTMX (öğrenme süresi).
- **Etki:** `package.json`'a `bootstrap`, `alpinejs`, `vite` eklendi. Admin React paketi ayrı çalışma (Faz 4 sonrası).

## ADR-004 — Sunucu: Hostinger VPS + Traefik + Let's Encrypt

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi (UYSA mevcut altyapıdan)
- **Bağlam:** UYSA'nın halihazırda Hostinger VPS aboneliği var; Traefik tecrübesi mevcut.
- **Karar:** Ubuntu 22.04 + Docker + Traefik (otomatik Let's Encrypt SSL) + MariaDB 10.6 (MySQL 8 uyumlu) + Redis 7. AWS/GCP'a geçilmeyecek (MVP için maliyet).
- **Gerekçe:** Aylık maliyet düşük (~₺500), tecrübeli; ölçeklenince AWS Lightsail veya Hetzner'a geçiş kolay.
- **Alternatif:** AWS EC2 + RDS (10x maliyet), Vercel + PlanetScale (PHP'ye uygun değil).
- **Etki:** Faz 0.10-0.13 alt görevleri (VPS + DNS + SSL + staging) UYSA tarafından yapılacak.

## ADR-005 — Ödeme: iyzico birincil, PayTR yedek/B2B

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi
- **Bağlam:** Türkiye'de en yaygın iki kart ödeme sağlayıcısı; iyzico API ergonomisi daha iyi, PayTR taksitte daha esnek.
- **Karar:** Birincil iyzico (kart, 3D Secure, otomatik komisyon ayrımı), PayTR yedek (taksit + B2B kurumsal kart). Banka havalesi paralel (manual onaylı).
- **Gerekçe:** iyzico Türk müşterinin tanıdığı; PayTR'nin "Bireysel" ve "İşyeri" modu B2B akışı kolaylaştırır.
- **Alternatif:** Stripe (Türkiye'de sınırlı), Garanti Sanal POS (entegrasyonu eski).
- **Etki:** `.env.example`'da iki sağlayıcı için API key alanı; `app/Services/PaymentService.php` factory pattern ile sağlayıcı seçimi.

## ADR-006 — E-posta: PostMark birincil, SendGrid yedek

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi
- **Bağlam:** Transactional mail deliverability kritik (lead capture + sipariş bildirim).
- **Karar:** PostMark birincil (transactional fokus, %99+ deliverability). SendGrid yedek (PostMark down olduğunda fallback).
- **Gerekçe:** PostMark Türkiye gönderimlerinde Gmail/Hotmail spam'a düşmüyor; SendGrid promosyon mailde daha güçlü.
- **Alternatif:** Mailgun (deliverability düşük), AWS SES (DNS karmaşıklığı).
- **Etki:** `app/Services/MailService.php` provider abstraction; PostMark fail olunca otomatik SendGrid'e düşüş.

## ADR-007 — Cache, Queue, Session, Rate Limit: Redis 7

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi
- **Bağlam:** PRD birden fazla yerde Redis varsayıyor (rate limit, cache, queue).
- **Karar:** Tek Redis instance (DB 0 cache, DB 1 queue, DB 2 sessions, DB 3 rate limit) — VPS aynı makinede çalışır.
- **Gerekçe:** İşlem hızı kritik; tek noktadan yönetim; Redis 7'nin streams + ACL özellikleri yeterli.
- **Alternatif:** Memcached (queue desteklemez), DB-tabanlı session (yavaş).
- **Etki:** `.env.example`'da `REDIS_HOST/PORT/PASSWORD/DB`; `predis/predis` composer paketi.

## ADR-008 — İlk 6 ay sıfır komisyon (pilot kampanya)

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi (PRD bölüm 12 uyarınca)
- **Bağlam:** Yemekçilerin platforma güveni MVP'nin başarı koşulu; ilk 100 yemekçiyi çekmek için risk azaltma.
- **Karar:** Yemekçi onaylandıktan sonra ilk 6 ay tüm kanallarda komisyon **%0**. 7. aydan itibaren PRD bölüm 12 oranları.
- **Gerekçe:** Pazar girişi için klasik land-and-expand; UYSA'nın pazarlama mesajı ("Sıfır risk başla").
- **Alternatif:** İlk 3 ay komisyon (ekibin ilk önerisi); kademeli (%2-4-6 artış).
- **Etki:** `commission_rules` tablosu (Faz 1'de eklenecek) `pilot_zero_until` alanı tutar; sipariş başına `app/Services/CommissionCalculator.php` bu tarihi kontrol eder. `.env.example: COMMISSION_FREE_PILOT_MONTHS=6`.

## ADR-009 — Enflasyon hesaplayıcı: 3 panel + özel formül desteği (genişletildi)

- **Tarih:** 2026-05-08 (revize: 2026-05-08)
- **Durum:** Kabul edildi (kullanıcı talebiyle kapsam genişletildi)
- **Bağlam:** Kullanıcı talebi (2026-05-08): yemek fiyatı için tarih bazlı enflasyon hesaplayıcı butonu. PRD v3.0'da yok. İkinci karar turunda kapsam genişletildi: 3 panelde de gösterilecek + admin **kendi isimli formülleri ekleyebilecek** (UYSA Et Endeksi, UYSA Sebze Endeksi gibi).
- **Karar:** Enflasyon hesaplayıcı **3 panelde de** sunulacak:
  - **Müşteri tarafı:** `/araclar/enflasyon-hesaplayici` (anasayfa, SEO + lead capture)
  - **Yemekçi paneli:** `/yemekci/araclar/enflasyon` (kendi maliyet/menü fiyatlarını güncellemek için)
  - **Admin paneli:** `/yonetim/araclar/enflasyon` (denetim + uyarı kurma + formül yönetimi)
  Veri kaynakları: TÜİK TÜFE genel, TÜİK TÜFE gıda alt grubu, TÜİK Yİ-ÜFE (üçü TCMB EVDS API), ENAG TÜFE (admin manuel) **+ özel isimli formüller** (admin oluşturur ve aylık değer girer; `is_official=false` flag).
- **Gerekçe:** Üç panel kullanıcısının da farklı senaryoları var (denetim/güncelleme/SEO); özel formüller UYSA'nın kendi sektörel endeksini oluşturmasına izin verir (rekabet avantajı). PRD'ye **Bölüm 25 — Enflasyon Hesaplayıcı** eklenecek.
- **Alternatif:** Sadece müşteri (eksik), sadece sabit 4 kaynak (esnek değil), tüm formüller scraping (hukuki risk).
- **Etki:** **Şema değişti** — `inflation_sources` (id, code, name, is_official, source_type ENUM('tuik_api','enag_manual','custom_admin'), tuik_evds_code, owner_admin_id, created_at) + `inflation_indices.source_id BIGINT` (ENUM yerine FK). Yeni route'lar 3 panelde. `app/Services/InflationCalculator.php` 3 panelde paylaşılan servis. PRD'ye Bölüm 25 yazılacak.

## ADR-010 — Faz 3.5: Admin "Teklif Pivot" + manuel yemekçi ekleme

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi
- **Bağlam:** Kullanıcı talebi (2026-05-08): "her şey admin panelinden yönetilmeli; kim kime teklif vermiş izlenmeli". Demo admin paneldeki "Teklifler" modülü placeholder; PRD bölüm 4.5 detaylı ama UI implementasyonu yok.
- **Karar:** Faz 3 (wizard) sonrası **Faz 3.5** olarak admin tarafında pivot/timeline + manuel yemekçi ekleme akışı tek başına bir iş paketi. Pilot operasyon için kritik.
- **Gerekçe:** UYSA saha ekibi telefonla anlaştığı yemekçiyi panelden açacak; teklif iz takibi günlük operasyonun bel kemiği.
- **Alternatif:** Faz 1'e ekle (DB+Auth ile karışır, riski artırır), Faz 7'ye ertele (pilot operasyonu körleştirir).
- **Etki:** Yeni servisler: `QuotePivotService`, `SupplierAdminService`, `AuditLogService` genişletme. Yeni admin route'lar (`/yonetim/yemekciler/yeni`, `/yonetim/teklifler/pivot`, `/yonetim/teklifler/akis`). 2 haftalık iş paketi.

## ADR-011 — Faz 3 sezgisel "Hızlı Teklif" modu (3 soru, 60 saniye)

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi
- **Bağlam:** Kullanıcı talebi (2026-05-08): "kullanıcılar çok kolay sezgisel şekilde teklif alabilmeli". PRD'deki 9 soru wizard mobil ilk-ziyarette uzun.
- **Karar:** Anasayfa hero CTA'da **3 soru hızlı teklif** (kişi sayısı + öğün + tarih+lokasyon) varsayılan; "Detaylı yapmak istersen" linki 9 soruya yönlendirir. A/B testi (%50/%50) Faz 3 lansmanında ölçülür.
- **Gerekçe:** Mobil dönüşüm artırma; PRD'nin 9 soru detayı kaybedilmez (link).
- **Alternatif:** Sadece 9 soru (statüko, mobilde drop-off yüksek), tek soru (yetersiz veri).
- **Etki:** Yeni servis: `QuickQuoteService`, `SimilarRequestFinder`, `SmartDefaultsHelper`. Yeni route: `/hizli-teklif`. Faz 3'e +1 hafta.

## ADR-012 — TÜİK verisi: TCMB EVDS API (TÜİK API değil)

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi (Faz 0.5 başında doğrulanacak)
- **Bağlam:** TÜİK'in açık veri portalı CSV/Excel sağlar (otomasyon zor). TCMB EVDS (Elektronik Veri Dağıtım Sistemi) JSON API döndürür ve TÜİK endekslerini aynı tutar.
- **Karar:** Birincil veri kaynağı **TCMB EVDS** (`evds2.tcmb.gov.tr/service/`) — TÜFE genel (`TP.FG.J0`), TÜFE gıda alt grubu (`TP.FG.J01`), Yİ-ÜFE (`TP.FE.OKTG01`). ENAG için admin manuel girişi. Endeks kodları Faz 0.5 başında final doğrulama yapılacak (TCMB veri tabanı zaman zaman güncelleniyor).
- **Gerekçe:** JSON API entegrasyonu sürdürülebilir; ücretsiz; resmi kaynak.
- **Alternatif:** TÜİK Excel scraping (kırılgan), 3. parti API (maliyetli).
- **Etki:** `app/Services/InflationDataFetcher.php` EVDS HTTP istemcisi. EVDS hesabı UYSA tarafından açılacak (ücretsiz, e-Devlet ile).

## ADR-013a — Backend framework: Laravel 11 (ADR-001 ek karar)

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi
- **Bağlam:** ADR-001 framework seçimini Faz 1 başına ertelemişti. Kullanıcı bu 3 yeni özelliği (enflasyon, admin pivot, hızlı teklif) **Faz 1 ile paralel** geliştirmek istediği için karar vakti şimdi geldi.
- **Karar:** **Laravel 11** kullanılacak. Saf PHP'den vazgeçildi.
- **Gerekçe:**
  1. Eloquent ORM `inflation_sources`/`inflation_indices` gibi karmaşık ilişkileri ve soft delete'i hazır verir.
  2. Migration sistemi (`database/migrations/*.php`) PRD'nin "direkt SQL ASLA" kuralına en uygun.
  3. Queue (`Jobs/`) — `FetchInflationIndicesJob`, mail gönderim için zorunlu.
  4. Blade — admin panel + müşteri sayfaları için hızlı.
  5. Sanctum/Fortify auth — Faz 1'i hızlandırır.
  6. Türkçe kaynak ve ekosistem yaygın.
- **Alternatif:** Saf PHP (yavaş, KVKK akışı için fazla manuel iş), Symfony (öğrenme süresi), CodeIgniter (modern değil).
- **Etki:** `composer.json` Laravel 11 paketleriyle güncellenecek (`laravel/framework`, `laravel/sanctum`, `spatie/laravel-permission`). `app/` yapısı Laravel'e uyumlu (`app/Http/Controllers/`, `app/Models/`). `composer install` Faz 1.0a başında çalıştırılacak (kullanıcı onayıyla).

## ADR-013b — Enflasyon: 4 panel + özel admin formülleri (ADR-009 üzerine)

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi
- **Bağlam:** ADR-009 başlangıçta sadece müşteri (anasayfa) için planlanmıştı. Kullanıcı kararı genişletti: **3 panelde** + **admin'in oluşturduğu özel isimli formüller**.
- **Karar:** Enflasyon hesaplayıcı modülü 3 panelde (admin/yemekçi/müşteri) görünür. Admin paneli ayrıca **kaynak yönetimi** sağlar — UYSA kendi sektörel endekslerini (`UYSA Et Endeksi`, `UYSA Sebze Endeksi` vb.) oluşturabilir, aylık değerleri elle girebilir.
- **Gerekçe:** UYSA'nın saha verisinden kendi endeksini üretmesi, rekabet avantajı + müşterilere "yemekçi sektörü için en doğru endeks" mesajı.
- **Alternatif:** Sadece resmi 4 kaynak (esnek değil), tüm formülleri kullanıcının yazması (güvenlik riski — formula injection).
- **Etki:** `inflation_sources` tablosu sabit ENUM yerine satır bazlı; `source_type ENUM('tuik_api','enag_manual','custom_admin')` + `tuik_evds_code` + `is_official` + `created_by_admin_id`. Migration dosyaları Faz 0.5 başında yazılır.

## ADR-013c — Geliştirme stratejisi: paralel iş paketleri (revize)

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi (kullanıcı seçimi)
- **Bağlam:** Plan dosyası başlangıçta seri bir faz akışı önermişti (Faz 0.5 → Faz 1 → ...). Kullanıcı paralel geliştirme istedi.
- **Karar:** Faz 1 (DB + Auth + KYC) ile **Faz 0.5 (enflasyon)** + **Faz 3.5 (admin pivot)** + **Faz 3 hızlı teklif iyileştirmeleri** paralel iş paketleri olarak yürütülür. Tek bir Claude oturumunda tek paket; oturumlar arası git dalları ile çakışma minimuma iner.
- **Gerekçe:** Kullanıcı önceliklendirmesi; 3 yeni özelliğin temel akışla bağımsız olması (enflasyon DB'den ayrı, admin pivot DB'ye paralel, hızlı teklif wizard üzerine katman).
- **Alternatif:** Seri (geç çıkar), tek mega oturum (riskli, context window).
- **Etki:** Branch stratejisi: `feature/faz-1-db-auth`, `feature/faz-0.5-enflasyon`, `feature/faz-3.5-admin-pivot`, `feature/faz-3-hizli-teklif`. PR review sonrası `dev` dalına merge. Mevcut `claude/start-yemekhaneci-project-xCJFu` Faz 0 entry'si; bu commit sonrası kapanır.

## ADR-015 — Faz 0.5 sonu: composer install + minimal Laravel-uyumlu deps

- **Tarih:** 2026-05-09
- **Durum:** Kabul edildi
- **Bağlam:** Faz 0.5 tamamlandığında composer install denemesi yapıldı. Orijinal composer.json Laravel-stili paketler içeriyordu (Symfony 7, Intervention Image, predis, iyzipay vb.) ama henüz hiçbiri kullanılmıyordu ve bazıları PHP eklentileri (ext-imagick, ext-redis) gerektiriyordu — sandbox ortamında install başarısız olurdu.
- **Karar:** composer.json minimumda tutuldu — sadece şu an gerçekten kullanılan/kullanılacak paketler:
  - `vlucas/phpdotenv` (.env desteği — Faz 1.0a config)
  - `monolog/monolog` (Laravel kurulduğunda log altyapısı)
  - `guzzlehttp/guzzle` (EVDS LIVE mode için — şu an file_get_contents)
  - `phpunit/phpunit` (test runner — TestRunner.php kaldırıldı)
- **Faz 1.0a'da eklenecekler** (Laravel kurulduğunda doğal gelecekler):
  `laravel/framework`, `laravel/sanctum`, `spatie/laravel-permission`,
  `predis/predis`, `intervention/image`, `iyzico/iyzipay-php`, vs.
- **Etki:** `composer install` ~50 paket kuruyor, `vendor/` 18MB, autoload.php 1834 sınıf. PHPUnit ile 33 test, 83 assertion, 22ms'de geçiyor. `public/index.php` Composer autoloader varsa onu kullanıyor (yoksa raw PHP fallback). Aynı kod hem dev hem (gelecek) Laravel'de çalışacak.

## ADR-014 — Faz 0.5 demo: raw PHP prototip (geçici)

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi (geçici — Faz 1.0a'da değiştirilecek)
- **Bağlam:** Kullanıcı talebi: "Yemekçi paneline tıklayarak girilmesin, login olsun". Demo hesaplar `OFU/1234` (admin) ve `Uysa/1234` (yemekçi). ADR-013a Laravel 11 seçti ama `composer install` henüz çalıştırılmadı; demo'yu kısa sürede teslim edebilmek için raw PHP prototipi yazıldı.
- **Karar:** Faz 0.5 boyunca (yaklaşık 1-2 hafta) demo iskelet **raw PHP** olarak çalışır:
  - `public/index.php` — front controller + PSR-4 autoloader
  - `app/Auth/SimpleAuth.php` — session-based, Argon2id hash'li demo kullanıcılar
  - `app/Http/Router.php` + `app/Middleware/AuthMiddleware.php` — basit pattern eşleştirme + role guard
  - `app/Helpers/functions.php` — view(), e(), csrf, redirect, flash
  - `config/auth.php` — sadece 2 demo kullanıcı (DB henüz yok)
  - `resources/views/*.php` — Blade yerine düz PHP template (Bootstrap 5 + Alpine.js + Chart.js CDN'den)
- **Faz 1.0a'da yapılacak değişim:**
  1. `composer install` (Laravel 11 paketleri)
  2. `app/Auth/SimpleAuth.php` → Laravel `auth` middleware + `App\Models\User`
  3. `config/auth.php` (demo) → `database_seeders/UserSeeder.php` (DB tabanlı)
  4. `public/index.php` (custom router) → Laravel router (`routes/web.php`)
  5. View'lar → `*.blade.php` (yine Bootstrap 5)
  6. URL'ler ve UI **birebir aynı kalacak** (`/giris-yap`, `/yemekci`, `/yonetim`, `/araclar/enflasyon-hesaplayici`)
- **Gerekçe:** Demo'yu hemen teslim et (ROI), sonra paketle değiştir. Hard dependency yok.
- **Alternatif:** Bekle composer install kurulumunu, demo gecikir. Hard-coded HTML, demo'da işe yaramaz.
- **Etki:** ~750 satır PHP demo kodu. Faz 1.0a'da bu kodun ~%80'i silinir, ~%20'si (servisler, controller imzaları, view yapıları) Laravel'e taşınır. Yatırım kaybı küçük.

**Demo doğrulama (2026-05-08, 27 dakikada teslim):**
- ✅ `Uysa/1234` → `/yemekci`, `OFU/1234` → `/yonetim`
- ✅ Yanlış şifre → 302 + flash hata
- ✅ Admin oturumuyla `/yemekci` → 403
- ✅ CSRF korumasız POST → 419
- ✅ Enflasyon hesabı: Mart 2024 5.000 ₺ → Mayıs 2026 10.998 ₺ (TÜFE Gıda, sentetik veri)
- ✅ Validation: tüm zorunlu alanlar tek tek raporlanıyor

## ADR-013 — Branch stratejisi: dev/staging/main + feature dalları

- **Tarih:** 2026-05-08
- **Durum:** Kabul edildi (CLAUDE.md uyarınca)
- **Bağlam:** Tek geliştirici Faz 1'e kadar; pilot sonrası ekip büyüyebilir.
- **Karar:** `main` (production, tag-based deploy), `staging` (auto-deploy on merge), `dev` (entegrasyon), `feature/*`/`fix/*`/`refactor/*` (geliştirme dalları). Mevcut `claude/start-yemekhaneci-project-xCJFu` Faz 0 dalıdır; merge sonrası silinecek.
- **Gerekçe:** Standart Git Flow benzeri; CI/CD entegrasyonu kolay.
- **Alternatif:** Trunk-based (tek dev için fazla risk), GitHub Flow (staging eksik).
- **Etki:** Faz 1 sonu staging deploy; her merge'de otomatik smoke test.

---

## Bekleyen Kararlar (Açık)

- **ADR-001 ek (Faz 1):** Saf PHP mı Laravel 11 mi? — Faz 1 başında ekip oturumu.
- **ADR-014 (Faz 4):** WhatsApp Business API sağlayıcı — direkt Meta mı, Twilio mu, Netgsm mı?
- **ADR-015 (Faz 6):** E-fatura sağlayıcı — ParamPos mu Foriba mı?
- **ADR-016 (Faz 7):** Yemekçi performans skor algoritması — basit ağırlıklı ortalama mı, Bayesian mı?

---

**Format:** Yeni karar eklerken numara artır, başlığı eklemeyi unutma. Statü değişikliği "Süperseded by ADR-XYZ" notu ile yapılır; kayıt silinmez.

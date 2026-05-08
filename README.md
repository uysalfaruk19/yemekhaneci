# Yemekhaneci.com.tr

> **Türkiye'nin Tarafsız Catering Pazaryeri** — kurumsal ve bireysel müşterileri yemekçi firmalarla şeffaf ve hızlı şekilde buluşturur.

UYSA Yemek Hizmetleri tarafından geliştirilen, **maliyet tabanlı şeffaflık** ve **anonim eşleştirme** üzerine kurulu PHP 8.2 + MySQL 8 platformu.

---

## İçerik

1. [Stack](#stack)
2. [Klasör Yapısı](#klasör-yapısı)
3. [Yerel Kurulum](#yerel-kurulum)
4. [Geliştirme Komutları](#geliştirme-komutları)
5. [Dokümantasyon Haritası](#dokümantasyon-haritası)
6. [Faz Yol Haritası](#faz-yol-haritası)
7. [Branch ve Commit Kuralları](#branch-ve-commit-kuralları)
8. [Lisans](#lisans)

---

## Stack

| Katman | Teknoloji |
|--------|-----------|
| Backend | PHP 8.2 (saf PHP veya Laravel 11 — Faz 1 başında karar verilecek) |
| Veritabanı | MySQL 8 (`utf8mb4_turkish_ci`) + Redis 7 |
| Frontend | Bootstrap 5 + Alpine.js + Vanilla ES6+ (jQuery yok) |
| Admin Panel | React 19 (karmaşık UI gerekirse) |
| Görsel | Imagick + WebP + Cloudflare CDN |
| Ödeme | iyzico (birincil), PayTR (taksit/B2B), Banka havalesi |
| E-Fatura | ParamPos veya Foriba |
| E-Posta | PostMark (birincil), SendGrid (yedek) |
| SMS | Netgsm (birincil), İletimerkezi (yedek) |
| Sunucu | Hostinger VPS — Ubuntu 22.04 + Traefik + Let's Encrypt SSL |
| Yedek | Günlük MySQL dump → Backblaze B2 (30 gün retention) |
| İzleme | Sentry + UptimeRobot |
| Harita | Leaflet + OpenStreetMap |
| Arama | MySQL FULLTEXT (Faz 5) → Meilisearch (ihtiyaç olursa) |

Detay: [PRD bölüm 14.1](PRD_v3.0.md#141-stack).

---

## Klasör Yapısı

```
yemekhaneci/
├── public/                      # Web kök (sadece index.php buradan açılır)
│   ├── assets/                  # css, js, img (build çıktıları)
│   └── uploads/                 # kullanıcı yüklemeleri (production: B2)
├── app/
│   ├── Controllers/
│   │   ├── Admin/               # /yonetim
│   │   ├── Supplier/            # /yemekci
│   │   ├── Customer/            # /hesabim
│   │   └── Public/              # anasayfa, wizard, anonim liste
│   ├── Models/                  # Eloquent / saf PDO modelleri
│   ├── Services/                # iş mantığı (PriceCalculator, MailService, ...)
│   ├── Repositories/            # DB sorguları
│   ├── Middleware/              # auth, csrf, rate-limit, kvkk
│   ├── Validators/              # whitelist input doğrulama
│   ├── Helpers/                 # küçük yardımcı fonksiyonlar
│   └── Jobs/                    # queue jobları (mail, fatura, hatırlatma)
├── config/                      # .env'den okunan ayarlar
├── database/
│   ├── migrations/              # versiyonlu şema değişiklikleri
│   └── seeders/                 # başlangıç verisi (kategoriler vb.)
├── resources/
│   ├── views/                   # HTML şablonları (public/admin/supplier/customer)
│   └── lang/tr/                 # Türkçe dil dosyaları
├── routes/                      # web.php / api.php / admin.php / supplier.php
├── storage/                     # log, cache, session, framework
├── tests/                       # Unit + Feature testler
├── docs/                        # api.md, deployment.md, runbook'lar
├── .env.example                 # ortam değişkeni şablonu
├── composer.json                # PHP bağımlılıkları
├── package.json                 # Frontend build bağımlılıkları
├── PRD_v3.0.md                  # ürün gereksinim dokümanı (kuzey yıldızı)
├── CLAUDE.md                    # geliştirme kuralları (zorunlu)
├── PROGRESS.md                  # faz ilerleme tablosu
├── DECISIONS.md                 # mimari karar kayıtları
└── README.md                    # bu dosya
```

PRD bölüm 14.2 ile birebir uyumludur.

---

## Yerel Kurulum

> **Ön koşul**: PHP 8.2+, Composer 2.x, Node.js 20+, MySQL 8, Redis 7, Git.

```bash
# 1) Repo'yu klonla
git clone git@github.com:uysalfaruk19/yemekhaneci.git
cd yemekhaneci

# 2) Ortam dosyasını hazırla
cp .env.example .env
# .env'yi düzenle (DB_PASSWORD, IYZICO_*, POSTMARK_TOKEN, ...)

# 3) PHP bağımlılıkları (Faz 1 başında çalıştırılacak)
composer install

# 4) Frontend bağımlılıkları (Faz 1 başında çalıştırılacak)
npm install

# 5) Veritabanını oluştur ve şemayı yükle
mysql -u root -p -e "CREATE DATABASE yemekhaneci CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;"
mysql -u root -p yemekhaneci < database_schema.sql

# 6) Geliştirme sunucusunu başlat
php -S localhost:8000 -t public/

# 7) Frontend asset watch (ayrı terminal)
npm run dev
```

Tarayıcıda aç: <http://localhost:8000>

> **Not**: Faz 0 yalnızca iskelet ve yapılandırma dosyalarını hazırlar. `composer install` ve `npm install` Faz 1 başında çalıştırılır.

---

## Geliştirme Komutları

```bash
composer lint        # PHP CodeSniffer (PSR-12)
composer cs-fix      # PHP-CS-Fixer ile otomatik düzelt
composer test        # PHPUnit
npm run dev          # Vite dev server
npm run build        # Production build
npm run lint:js      # ESLint
npm run lint:css     # Stylelint
npm run format       # Prettier
```

Test veritabanı: `yemekhaneci_test` (ayrı şema, `.env.testing` üzerinden).

---

## Dokümantasyon Haritası

| Doküman | Amaç | Ne Zaman Okumalı |
|---------|------|------------------|
| [`PRD_v3.0.md`](PRD_v3.0.md) | Tam ürün gereksinim dokümanı (24 bölüm) | Her oturumun başında |
| [`CLAUDE.md`](CLAUDE.md) | Kod yazım kuralları, güvenlik, KVKK | Her commit öncesi |
| [`database_schema.sql`](database_schema.sql) | 28 tablo, çalıştırılabilir SQL | Faz 1 |
| [`00_README_Baslangic.md`](00_README_Baslangic.md) | Geliştirici paketi giriş | İlk gün |
| [`04_Claude_Code_Baslangic_Komutlari.md`](04_Claude_Code_Baslangic_Komutlari.md) | 16 oturumluk Claude Code komutları | Her oturum |
| `Yemekhaneci_Tam_Gelistirici_Paketi/` | Demo HTML, hukuki, marka, pilot rehberi | Referans |
| `PROGRESS.md` | Faz ilerleme tablosu | Her faz sonu |
| `DECISIONS.md` | Mimari karar kayıtları (ADR) | Her büyük karar |

Demo HTML referansları (kopya-yapıştır değil, davranış referansı):

- `yemekhaneci-fiyat-hesaplama.html` — 9 soruluk müşteri wizard'ı
- `yemekci-maliyet-paneli.html` — yemekçi maliyet matrisi (6 sekme)
- `yemekhaneci-admin-panel.html` — 12 modüllü admin panel

---

## Faz Yol Haritası

| Faz | Süre | Çıktı |
|-----|------|-------|
| **Faz 0** — Setup | 1 hafta | İskelet, .env, composer/package, repo, VPS, DNS, SSL |
| **Faz 1** — Temel | 3 hafta | DB migration, Auth (3 tip), yemekçi onay (KYC) |
| **Faz 2** — Maliyet | 3 hafta | Yemekçi panel, 6 sekmeli maliyet matrisi, canlı önizleme |
| **Faz 3** — Wizard | 3 hafta | 9 soru anasayfa, fiyat motoru, anonim yemekçi listesi |
| **Faz 4** — Mail | 2 hafta | Lead capture, mail sistemi, KVKK akışları |
| **Faz 5** — Menü | 2 hafta | Menü kataloğu, müşteri tarama, FULLTEXT arama |
| **Faz 6** — Sipariş | 3 hafta | Sepet, ödeme (iyzico/PayTR), sipariş yönetimi |
| **Faz 7** — Teklif | 2 hafta | Reaktif teklif, mesajlaşma, yorum/değerlendirme |
| **Faz 8** — Beta | 3 hafta | Pilot test (30-40 yemekçi), hata düzeltme, performans |
| **Faz 9** — Lansman | Sürekli | PR, içerik, büyüme |

**Toplam:** ~22 hafta MVP, ~26 hafta açık lansman.
Detaylı durum: [PROGRESS.md](PROGRESS.md).

---

## Branch ve Commit Kuralları

- `main` → production (yalnız tag ile deploy)
- `staging` → staging server (her merge'de auto-deploy)
- `dev` → entegrasyon
- `feature/*`, `fix/*`, `refactor/*` → geliştirme dalları

**Commit formatı (Türkçe + Conventional Commits):**

```
feat(menu): tedarikçi menü oluşturma sayfası eklendi
fix(payment): iyzico callback hatası düzeltildi
refactor(auth): user_type kontrolü middleware'e taşındı
docs(prd): bölüm 7.2 wizard akışı güncellendi
chore(deps): vlucas/phpdotenv 5.6 yükseltildi
```

Detay kurallar: [CLAUDE.md](CLAUDE.md).

---

## Komisyon Yapısı

| Kanal | Oran |
|-------|------|
| Direkt sipariş | %8–12 |
| Reaktif teklif | %5–7 |
| Proaktif paket | %8–12 |
| B2B tedarik | %3–5 |

**Pilot kampanya:** İlk 6 ay sıfır komisyon. Detay: [PRD bölüm 12](PRD_v3.0.md).

---

## Marka

```css
--brand-primary: #6B1F2A;   /* derin bordo */
--brand-accent:  #C9A961;   /* eski altın */
--brand-cream:   #FAF6F0;   /* krem zemin */
```

Fontlar (Google Fonts CDN üzerinden):

- Başlık: **Cormorant Garamond**
- Gövde: **Manrope**
- Sayısal: **JetBrains Mono**

---

## Lisans

© 2026 UYSA Yemek Hizmetleri — Yemekhaneci.com.tr
Bu repo özel mülkiyettir; izinsiz kopyalanamaz, dağıtılamaz.

---

**İletişim:** Ömer M. (Süper Admin), Emrullah Gökhan (Operasyon), Emre Köse (Operasyon)

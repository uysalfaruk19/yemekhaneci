# CLAUDE.md — Yemekhaneci.com.tr Geliştirme Rehberi

> Bu dosya Claude Code için **uyulması zorunlu kurallar** içerir.
> Her oturumda PRD.md ile birlikte oku.

---

## 🎯 Proje Özeti

**Yemekhaneci.com.tr** — Türkiye'nin catering pazaryeri.
- **Sahip:** UYSA Yemek Hizmetleri (Ömer)
- **Stack:** PHP 8.2 + MySQL 8 + Bootstrap 5 + Alpine.js
- **Hosting:** Hostinger VPS (Ubuntu 22.04) + Traefik + SSL
- **Detay spec:** PRD.md (her zaman okumalı kuzey yıldızı)

---

## 📋 Genel Çalışma Kuralları

### Dosya ve Kod Üretimi
1. **Tek seferde 1000 satırı geçme** — büyük modülleri parçalara böl
2. **Her dosya tek bir sorumluluk taşısın** (SRP)
3. **PSR-12 PHP standardı** — formatlama, isimlendirme
4. **DRY prensibi** — aynı kodu iki kere yazma
5. **Magic number/string yok** — `config/` veya `enum` kullan

### İletişim
1. **Yorumlar Türkçe** (sadece kod içi değil, README dahil)
2. **Commit mesajları Türkçe** + Conventional Commits formatı:
   - `feat(menu): tedarikçi menü oluşturma sayfası eklendi`
   - `fix(payment): iyzico callback hatası düzeltildi`
   - `refactor(auth): user_type kontrolü middleware'e taşındı`
3. **Hata mesajları kullanıcıya Türkçe** gösterilsin

### Karar Disiplini
- Belirsizse SOR. Tahmin yapma, varsayım kurma.
- PRD'de açık olmayan bir şey görünce dur, sor.
- "Bonus özellik" eklemeyin — sadece istenen.

---

## 🗄️ Veritabanı Kuralları

### Migration Disiplini
- **Direkt SQL ASLA** — sadece migration dosyaları
- Migration adı: `YYYY_MM_DD_HHMMSS_aciklama.php`
- Her migration **geri alınabilir** olmalı (down() metodu)
- Foreign key'lerde `ON DELETE` davranışını AÇIKÇA belirt

### Şema Standartları
```sql
-- Her tabloda ZORUNLU sütunlar:
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

-- Soft delete kullanılan tablolarda:
deleted_at TIMESTAMP NULL DEFAULT NULL

-- Para alanları için:
amount DECIMAL(12,2) UNSIGNED NOT NULL  -- KESİNLİKLE FLOAT KULLANMA

-- Boolean alanları:
is_active TINYINT(1) NOT NULL DEFAULT 1  -- (PHP'de bool casting)

-- Enum yerine ENUM tipi kullan (string değil int değil):
status ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending'
```

### İndeks Kuralları
- Foreign key sütunları **otomatik indekslenmiş** olmalı
- Sıkça filtrelenen sütunlara indeks (status, user_type vb.)
- LIKE ile aranacak metin alanlarına FULLTEXT
- created_at DESC sıralanan tablolarda indeks zorunlu

### Karakter Seti
- Tüm tablolar `utf8mb4` + `utf8mb4_turkish_ci` (Türkçe sıralama doğru çalışsın)

---

## 🛡️ Güvenlik (TAVİZSİZ)

### Asla Yapma
- ❌ Direkt `$_POST` veya `$_GET` kullanma → her zaman validator'dan geçir
- ❌ String birleştirme ile SQL → her zaman prepared statement
- ❌ `echo $_GET['x']` → her zaman `htmlspecialchars()`
- ❌ Şifreyi `md5()` veya `sha1()` ile hash etme → `password_hash(PASSWORD_ARGON2ID)`
- ❌ API anahtarını koda yaz → `.env` + `getenv()`
- ❌ Hata mesajında DB içeriği göster → genel "İşlem başarısız" mesajı

### Her Zaman Yap
- ✅ **CSRF token** her form'da (GET hariç)
- ✅ **Rate limiting** login, register, talep oluşturma için (Redis tabanlı)
- ✅ **Input validation** her endpoint'te (whitelist yaklaşımı)
- ✅ **HTTPS zorunlu** — HTTP'den HTTPS'e 301 redirect
- ✅ **Session güvenliği** — `httponly`, `secure`, `samesite=lax`
- ✅ **File upload kontrolü:**
  - MIME type whitelist (jpg, png, webp, pdf)
  - Max boyut (görsel 5MB, PDF 10MB)
  - Random dosya adı (`uniqid()` + uzantı)
  - `uploads/` dışında çalıştırılabilir izin yok
- ✅ **2FA admin için zorunlu** (Google Authenticator)
- ✅ **Ödeme callback doğrulama** — gateway imzası kontrol edilmeden işlem yapma

---

## 🎨 Frontend Kuralları

### HTML
- **Semantic tags** kullan: `<header>`, `<nav>`, `<main>`, `<article>`, `<aside>`, `<footer>`
- **Accessibility:** her input'a `<label>`, butonlara `aria-label`, görüntülere `alt`
- **Mobile-first:** önce mobile, sonra desktop breakpoint

### CSS
- **Bootstrap 5 utility classes** ana yaklaşım
- Custom CSS minimum, sadece marka özel renkleri ve override
- **CSS değişkenleri** kullan: `--brand-primary`, `--brand-secondary`
- **px değil rem** font boyutları için

### JavaScript
- **Vanilla JS + Alpine.js** (jQuery YOK)
- Admin panelde React kullanılabilir (karmaşık UI gerekirse)
- **ES6+ syntax** (arrow function, const/let, template literal)
- Her event handler'da try/catch
- Console'a log atma (production'da kaldır)

### Performans
- **Resim optimizasyonu:** WebP + lazy loading (`loading="lazy"`)
- **CSS/JS minify** (production build)
- **CDN** statik varlıklar için (Cloudflare)
- **Lighthouse skoru** > 85 (performance)

---

## 🔌 API ve Endpoint Kuralları

### URL Yapısı
- RESTful: `GET /menus`, `POST /menus`, `GET /menus/{id}`, `PUT /menus/{id}`, `DELETE /menus/{id}`
- Türkçe URL slug'lar: `/menu/100-kisilik-cocktail-menusu` (SEO için)
- API endpoint'leri ayrı: `/api/v1/...`

### Response Formatı
```json
// Başarılı
{
  "success": true,
  "data": { ... },
  "message": "İşlem başarılı"
}

// Hata
{
  "success": false,
  "error": "VALIDATION_FAILED",
  "message": "E-posta geçersiz",
  "errors": { "email": ["Geçerli bir e-posta giriniz."] }
}
```

### HTTP Status Kodları
- `200` OK, `201` Created, `204` No Content
- `400` Bad Request (validation), `401` Unauthorized, `403` Forbidden
- `404` Not Found, `409` Conflict, `422` Unprocessable Entity
- `429` Too Many Requests (rate limit), `500` Internal Server Error

---

## 📦 Klasör ve İsimlendirme

### Klasör Yapısı (Sıkı)
```
yemekhaneci/
├── public/                 # Web kök (sadece index.php burada)
├── app/
│   ├── Controllers/        # *Controller.php
│   ├── Models/             # tekil isim, PascalCase
│   ├── Services/           # *Service.php (iş mantığı)
│   ├── Repositories/       # *Repository.php (DB sorguları)
│   ├── Middleware/         # *Middleware.php
│   ├── Validators/         # *Validator.php
│   └── Helpers/            # küçük yardımcı fonksiyonlar
├── config/                 # .php dosyaları, env'den okur
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/              # blade veya raw php
│   └── lang/tr/            # dil dosyaları
├── routes/
│   ├── web.php             # browser route'ları
│   └── api.php             # api route'ları
├── storage/                # log, cache, sessions
├── tests/
│   ├── Unit/
│   └── Feature/
├── .env.example            # şablon (gerçek .env repo'da DEĞİL)
├── .gitignore
├── composer.json
├── PRD.md                  # ürün gereksinimleri
├── CLAUDE.md               # bu dosya
└── README.md
```

### İsimlendirme
- **Dosya:** PascalCase (`UserController.php`, `OrderService.php`)
- **Class:** PascalCase
- **Method:** camelCase (`getUserById`, `calculateCommission`)
- **Variable:** camelCase (`$userId`, `$orderTotal`)
- **Constant:** UPPER_SNAKE_CASE (`MAX_UPLOAD_SIZE`)
- **DB Table:** snake_case çoğul (`users`, `order_items`)
- **DB Column:** snake_case (`first_name`, `created_at`)
- **CSS Class:** kebab-case (`menu-card`, `btn-primary`)
- **JS Variable:** camelCase

---

## 🧪 Test Disiplini

### Her Faz İçin
- **Manuel test checklist** yaz (kabul kriterleri)
- **Kritik akışlara unit test:** auth, payment, order creation
- **Test database** ayrı olsun (`yemekhaneci_test`)

### Test Komutları
```bash
# Migration test
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing

# Unit testler
./vendor/bin/phpunit

# Lint
composer lint
```

---

## 🚀 Deployment Kuralları

### Branch Stratejisi
- `main` → production (sadece tag ile deploy)
- `staging` → staging server (her merge'de auto-deploy)
- `dev` → development
- Feature: `feature/menu-creation`, `fix/payment-bug`

### Deploy Öncesi Kontrol Listesi
- [ ] `.env.production` ayarları doğru (DB, mail, SMS, payment)
- [ ] Migration'lar çalıştı, hata yok
- [ ] HTTPS aktif, SSL geçerli
- [ ] Backup alındı (DB + uploads)
- [ ] Cache temizlendi
- [ ] Manuel smoke test (kayıt, giriş, sipariş, ödeme)

### Backup Stratejisi
- **Günlük MySQL dump** → /var/backups/mysql/ → Backblaze B2
- **Haftalık uploads** → Backblaze B2
- **30 günlük retention** (eski olanlar otomatik silinir)
- **Restore tatbikatı** ayda bir (gerçekten çalışıyor mu?)

---

## 🇹🇷 Türkiye-Spesifik Gereksinimler

### KVKK Uyumu
- Çerez izni banner (zorunlu, opt-in)
- Aydınlatma metni footer'da link
- Kullanıcı verileri silme talebi formu (`/hesabim/veri-silme`)
- Veri ihlali durumunda bildirim akışı (admin + KVKK)

### E-Fatura
- Kurumsal müşterilere e-fatura zorunlu (€5.000+)
- ParamPos veya Foriba entegrasyonu
- Fatura numarası seri formatı: `YHC2026000001`

### Vergi
- KDV %10 yemek hizmetleri (kontrol et, değişebilir)
- Komisyon faturası ayrı (UYSA → Catering firma)
- Stopaj kontrolü (gerçek kişi tedarikçi varsa)

### SMS
- Netgsm veya İletimerkezi
- Türkçe karakter (UTF-8) destekli
- Header onaylı (UYSAYEMEK veya YEMEKHANECI)

---

## 🤖 Claude Code İle Konuşurken

### İdeal Komut Formatı
```
[Faz X.Y - kısa başlık]

Bağlam: PRD bölüm Z'yi oku.

Yapılacak:
- net liste
- net liste

Çıktı:
- üretilen dosyalar
- test komutları
- README'ye eklenecek bölüm

Kurallar:
- (varsa özel kurallar)
```

### Yasak Davranışlar (Claude Code için)
- ❌ "Bonus olarak şunu da ekledim" — sadece isteneni yap
- ❌ "Daha iyi olur diye değiştirdim" — değiştireceksen önce sor
- ❌ Composer/npm paketi kur, sormadan
- ❌ Migration ile var olan veriyi sil/değiştir, sormadan
- ❌ .env dosyasını commit'le

### Beklenen Davranışlar
- ✅ Belirsizse "Bunu şöyle anlıyorum, doğru mu?" diye sor
- ✅ Hata varsa root cause'u bul, üstünü örtme
- ✅ Her dosyada en üste 1-2 satır docblock (ne işe yarar)
- ✅ TODO'ları açık yaz: `// TODO: Faz 4'te queue'ya taşınacak`

---

## 📚 Yardımcı Kaynaklar

- PRD: `PRD.md` (bu klasörde)
- DB diagramı: (oluşturulacak — dbdiagram.io)
- Wireframes: (oluşturulacak — Figma)
- API dokümanı: `docs/api.md` (her endpoint eklenince güncelle)

---

**Son güncelleme:** 7 Mayıs 2026
**Versiyon:** 1.0

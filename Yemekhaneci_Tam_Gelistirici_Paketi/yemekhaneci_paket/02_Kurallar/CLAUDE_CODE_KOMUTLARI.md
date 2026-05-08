# Claude Code — Yemekhaneci.com.tr Başlangıç Komut Paketi

Bu dosya, Claude Code'a vereceğiniz **ilk 8 hafta için hazır komut şablonlarını** içerir.
Her bölümü kopyala/yapıştır mantığıyla kullan.

---

## 🚀 OTURUM 1: Proje Setup (1-2 saat)

### Komut 1.1 — VPS Hazırlık
```
PRD.md ve CLAUDE.md dosyalarını oku.

Görev: Hostinger VPS (Ubuntu 22.04) üzerinde yemekhaneci.com.tr için altyapı kuracak bash scripti yaz.

Kurulacaklar:
- Nginx + PHP 8.2-FPM + MySQL 8 + Redis
- Composer (global)
- Node.js 20 LTS + npm
- Certbot (Let's Encrypt SSL)
- UFW firewall (sadece 22, 80, 443)
- Fail2ban (SSH brute force koruması)
- Otomatik DB backup cron (günlük)

Çıktı:
- /home/claude/scripts/vps-setup.sh
- /home/claude/scripts/backup-db.sh
- README'ye "VPS Kurulum" bölümü
- Çalıştırma komutları
```

### Komut 1.2 — Proje İskelet
```
PRD.md bölüm 6.2'deki klasör yapısını kur.

Yapılacaklar:
- Tüm klasörleri oluştur (boş .gitkeep ile)
- composer.json (PHP 8.2, vlucas/phpdotenv, ramsey/uuid)
- package.json (bootstrap@5.3, alpinejs, axios)
- .env.example (PRD'deki tüm config'lerle)
- .gitignore (vendor, node_modules, .env, storage/logs/*)
- public/index.php (basit "Hello Yemekhaneci" döner)
- README.md (kurulum talimatları)

Çıktı:
- Çalışır iskelet
- composer install + npm install komutları
- localhost:8000'de test edilebilir hale getir
```

### Komut 1.3 — Domain ve SSL Bağlama
```
yemekhaneci.com.tr domain'i için Nginx config + Let's Encrypt SSL kur.

Yapılacaklar:
- /etc/nginx/sites-available/yemekhaneci.com.tr config dosyası
- HTTP → HTTPS 301 redirect
- PHP-FPM Unix socket bağlantısı
- Static dosyalar için cache header (1 yıl)
- gzip + brotli aktif
- Security header'lar (HSTS, CSP, X-Frame-Options vs.)
- Certbot ile SSL sertifika al, otomatik yenile

Çıktı:
- nginx config dosyası
- certbot komutu
- Test: curl -I https://yemekhaneci.com.tr (200 + HSTS header)
```

---

## 🗄️ OTURUM 2: Veritabanı (3-5 saat)

### Komut 2.1 — Migration Sistemi
```
PRD bölüm 7'deki 18 tablo için migration sistemi kur.

Yapılacaklar:
- database/migrations/ klasörüne her tablo için 1 migration dosyası
- Dosya isimleri: 2026_05_07_120001_create_users_table.php gibi
- Her migration'da up() + down() metodları
- Foreign key constraint'leri ON DELETE/UPDATE davranışı belirt
- CLAUDE.md'deki şema standartlarına UY (utf8mb4, decimal vs.)
- Migration runner (artisan benzeri basit CLI)
- Komut: php cli migrate, php cli migrate:rollback, php cli migrate:fresh

Çıktı:
- 18 migration dosyası
- cli script (php cli)
- README'ye "Veritabanı" bölümü ekle
- Test: php cli migrate:fresh hatasız çalışsın
```

### Komut 2.2 — Seeders (Test Verisi)
```
Geliştirme için test verisi oluştur.

Yapılacaklar:
- database/seeders/ altında her tablo için faker tabanlı seeder
- 1 admin user (email: admin@yemekhaneci.com.tr, şifre: secret)
- 5 kurumsal müşteri
- 10 bireysel müşteri
- 15 onaylı tedarikçi
- Her tedarikçi için 5-10 menü
- 30 örnek sipariş (farklı statülerde)
- 20 özel teklif talebi + bunlara teklifler
- 50 yorum

Türkçe gerçekçi veri kullan (Türkçe isimler, Türk şehirleri, gerçekçi yemek isimleri).

Çıktı:
- Seeder dosyaları
- Komut: php cli db:seed
- README'ye seeder kullanımı
```

---

## 🔐 OTURUM 3: Auth Sistemi (4-6 saat)

### Komut 3.1 — Kullanıcı Kayıt/Giriş
```
PRD bölüm 8.1'deki giriş/kayıt sayfaları için auth sistemi yaz.

Yapılacaklar:
- POST /kayit — 3 user_type için tek endpoint (kurumsal, bireysel, tedarikçi)
- POST /giris — e-posta + şifre (Argon2id)
- POST /sifre-sifirla — token bazlı, 1 saat geçerli
- GET /eposta-dogrula/{token} — e-posta verification link
- Rate limiting (Redis): 5 login denemesi / 15 dk
- Session yönetimi (httponly, secure, samesite=lax cookie)
- 2FA (Google Authenticator) — admin için ZORUNLU
- KVKK aydınlatma onay checkbox (zorunlu)

Frontend:
- /giris, /kayit, /sifre-sifirla sayfaları (Bootstrap 5)
- Mobil uyumlu, Türkçe error mesajları
- Tedarikçi kaydı için ekstra alanlar (firma adı, vergi no, telefon)

Güvenlik:
- CSRF token zorunlu
- Şifre min 8 karakter, en az 1 büyük + 1 sayı
- E-posta verification olmadan giriş yapamaz
- Brute force koruması

Çıktı:
- Controller: AuthController.php
- Service: AuthService.php
- Middleware: AuthMiddleware.php, GuestMiddleware.php
- Validators
- Views: auth/login.php, auth/register.php, auth/reset.php
- Test: PHPUnit feature testleri (login flow)
```

### Komut 3.2 — Tedarikçi Onay Akışı
```
PRD bölüm 8.4'teki admin tedarikçi onay paneli.

Yapılacaklar:
- Tedarikçi kayıt sonrası status='pending_approval'
- Admin paneli: /yonetim/tedarikciler/onay-bekleyen
  - Liste: firma adı, vergi no, başvuru tarihi, belgeler
  - Detay: tüm bilgileri görüntüle
  - Aksiyonlar: Onayla / Reddet (sebep notu) / Ek Belge İste
- Onay durumunda: e-posta + SMS bildirimi tedarikçiye
- Red durumunda: sebep ile birlikte e-posta
- Onaylı tedarikçi profile düzenleme erişimi açılır

Çıktı:
- Controller: Admin/SupplierApprovalController.php
- Views: admin/suppliers/pending.php, admin/suppliers/show.php
- E-posta template'leri (resources/views/emails/)
- Test: bir kayıt → admin onay → tedarikçi giriş yapabilir
```

---

## 📋 OTURUM 4: Tedarikçi Profil & Menü (4-6 saat)

### Komut 4.1 — Tedarikçi Profil Sayfası
```
PRD bölüm 8.3'teki tedarikçi profil yönetimi.

Yapılacaklar:
- /tedarikci/profil sayfası
- Düzenlenebilir alanlar: logo, kapak görseli, bio (max 500 char), kuruluş yılı, günlük kapasite, mutfak adresi
- Hizmet bölgesi: harita üzerinde poligon çizme (Leaflet + OSM)
- Belge yükleme: ISO 22000, HACCP, vergi levhası, ticari sicil
- Belge expiry tarihi takibi (30 gün kala uyarı)
- Profil önizleme: /firma/{slug} ile aynı görünüm

Görsel yönetimi:
- Logo: 500x500 max, otomatik kare crop
- Kapak: 1920x600 banner
- WebP'ye otomatik dönüşüm
- public/uploads/suppliers/{supplier_id}/

Çıktı:
- Controller: Supplier/ProfileController.php
- Service: ImageUploadService.php (resize, watermark, webp dönüşüm)
- Service: GeoService.php (poligon doğrulama)
- Views: supplier/profile/edit.php, profile/preview.php
- JS: leaflet poligon çizim modülü
```

### Komut 4.2 — Menü Yönetimi
```
PRD bölüm 8.3'teki menü oluşturma/düzenleme.

Yapılacaklar:
- /tedarikci/menuler — liste sayfası (filtreleme, arama)
- /tedarikci/menuler/yeni — menü oluşturma formu
- /tedarikci/menuler/{id}/duzenle — düzenleme
- Menü alanları: ad, kategori (PRD'deki kategorilerden), açıklama, fiyat, min/max kişi, hazırlık süresi
- Menü içeriği (menu_items): yemek ekle/sil/sırala (drag-drop, Alpine.js)
- Çoklu fotoğraf yükleme (max 10, sürükle-bırak)
- Yayınla / Taslak durumu
- Önizleme (gerçek müşteri görünümü)

Validation:
- Ad min 5 char, max 100
- Açıklama min 50 char
- Fiyat: 50 TL - 500.000 TL arası
- En az 1 yemek + 1 fotoğraf zorunlu
- Yayınlamak için tedarikçi onaylı olmalı

Çıktı:
- Controller: Supplier/MenuController.php
- Service: MenuService.php
- Views: supplier/menus/* (4 sayfa)
- JS: çoklu fotoğraf upload + drag-drop sıralama
```

---

## 🛒 OTURUM 5: Müşteri Tarafı - Menü Tarama (3-5 saat)

### Komut 5.1 — Anasayfa
```
PRD bölüm 8.1'deki anasayfa.

Yapılacaklar:
- Hero section: "Türkiye'nin Catering Pazaryeri" + arama formu
- Kategori kartları (kahvaltı, öğle, kokteyl, ramazan vb.) — DB'den dinamik
- Öne çıkan menüler (en çok sipariş edilen 8)
- Öne çıkan tedarikçiler (en yüksek puanlı 6)
- "Nasıl çalışır?" — 3 adımlı görsel anlatım
- Tedarikçi davet CTA: "Catering firmasıysanız bize katılın"
- Footer: kurumsal linkler, sosyal medya, KVKK, sözleşme
- Lazy loading görüntüler için
- SEO: title, meta description, OpenGraph, Schema.org Organization

Çıktı:
- Controller: HomeController.php
- View: home.php
- CSS/JS optimize, Lighthouse 90+
```

### Komut 5.2 — Menü Listeleme + Filtreleme
```
PRD bölüm 8.1'deki menü tarama.

Yapılacaklar:
- /menuler sayfası — kart bazlı liste (responsive grid)
- Filtreler (URL parametreli, paylaşılabilir):
  - Kategori
  - Şehir / İlçe
  - Fiyat aralığı (slider)
  - Kişi sayısı
  - Mutfak türü
  - Sadece onaylı (HACCP/ISO)
- Sıralama: yeni, popüler, fiyat artan/azalan, puan
- Pagination (sayfa başı 24 menü)
- "Sonuç bulunamadı" halinde alternatif öneri

Performans:
- Filtreleme AJAX (sayfa yenilenmeden, Alpine.js)
- Önbellek: kategoriler 1 saat (Redis)
- DB query optimize: gerekli sütunlar, JOIN sayısı min

Çıktı:
- Controller: MenuListController.php
- Service: MenuFilterService.php
- View: menus/list.php
- JS: filter-handler.js (Alpine.js)
```

### Komut 5.3 — Menü Detay & Tedarikçi Profil
```
PRD bölüm 8.1'deki menü detay + tedarikçi sayfası.

Yapılacaklar:
- /menu/{slug} sayfası
  - Foto galeri (lightbox)
  - Açıklama, içerik (menu_items listesi)
  - Fiyat, min/max kişi, hazırlık süresi
  - Tedarikçi kartı (logo, ad, puan, link)
  - Müşteri yorumları (son 10)
  - "Sepete ekle" / "Teklif al" butonları
  - Benzer menüler (aynı kategori, ±%30 fiyat)
- /firma/{slug} sayfası
  - Tedarikçi bilgileri (bio, kapasite, kuruluş)
  - Belge rozeti (HACCP, ISO görsel)
  - Tüm menüleri (kategori bazlı)
  - Tüm yorumları (filtreleme: 5/4/3/2/1 yıldız)
  - Hizmet bölgesi haritası

SEO:
- URL slug'lar Türkçe (deunicode)
- Schema.org Product, Organization, Review markup
- Canonical URL

Çıktı:
- Controller: MenuDetailController.php, SupplierPublicController.php
- View: menus/show.php, suppliers/show.php
- JS: lightbox + AJAX yorum sayfalama
```

---

## 💳 OTURUM 6: Sipariş & Ödeme (5-7 saat)

### Komut 6.1 — Sepet & Sipariş Akışı
```
PRD bölüm 8.5 Flow A'daki sipariş akışı.

Yapılacaklar:
- Sepet (session bazlı, üyelik gerekmez)
- /sepet sayfası: menü, kişi sayısı, tarih seç, adres
- Tarih seçimi: tedarikçinin müsait günleri (calendar widget)
- Adres: mevcut kayıtlı adres + yeni adres ekle
- Sipariş özeti: ara toplam + KDV + komisyon = total
- Ödeme yöntemi seç: kart (iyzico) / havale / kurumsal cari
- Sipariş onay sayfası
- Sipariş numarası: YHCYYYYNNNNNNN formatında

Validation:
- Tarih min 24 saat sonra (acil siparişler için ayrı flow)
- Tedarikçinin müsaitlik takvimi kontrol
- Kişi sayısı menü min/max içinde
- Adres tedarikçinin hizmet bölgesinde

Çıktı:
- Controller: CartController.php, OrderController.php
- Service: OrderService.php
- Service: CommissionService.php
- View: cart/* (3 sayfa)
- E-posta: sipariş onay (müşteri + tedarikçi)
```

### Komut 6.2 — iyzico Ödeme Entegrasyonu
```
iyzico ile kart ödeme + 3D Secure.

Yapılacaklar:
- iyzipay-php SDK kurulumu
- Test ortamı .env config (sandbox)
- Ödeme akışı:
  1. Sipariş oluşturulur (status='awaiting_payment')
  2. iyzico checkout form başlatılır
  3. Müşteri 3D ekranına yönlendirilir
  4. Callback: iyzico imza doğrulama (KRİTİK)
  5. Başarılı: order status='accepted', payment kayıt, tedarikçiye bildirim
  6. Başarısız: order status='cancelled', kullanıcıya hata göster
- Iade akışı (admin paneli):
  - Tam iade
  - Kısmi iade (sebep notu)
- Webhook: iyzico'dan async durum güncellemeleri

Güvenlik:
- API key .env'de
- Callback URL signature kontrolü
- Idempotent (aynı transaction 2 kere işlenmesin)
- Tüm transactions activity_log'a yazılsın

Çıktı:
- Service: PaymentService.php (interface + IyzicoDriver)
- Controller: PaymentCallbackController.php
- Test: PHPUnit + iyzico sandbox
- README'ye iyzico setup adımları
```

### Komut 6.3 — Havale & Kurumsal Cari
```
Banka havalesi ve kurumsal cari hesap ödemesi.

Havale:
- Sipariş tamamlanırken "Havale ile öde" seçilir
- Ekran: banka bilgileri + sipariş no (açıklama olarak yaz)
- Order status='awaiting_transfer'
- 48 saat süre, dolduğunda otomatik iptal
- Admin panelinden manuel havale onay (görüldüğünde "Onayla" butonu)
- Onaylanınca order status='accepted'

Kurumsal Cari:
- Onaylı kurumsal müşterilere açılır (admin atar)
- Aylık limit (örn 50.000 TL)
- Sipariş anında stoktan düşülür (cari borç artar)
- Aylık fatura kesilir (her ayın 1'inde otomatik)
- Limit aşıldığında uyarı + sipariş bloke

Çıktı:
- Service: BankTransferService.php
- Service: CorporateCreditService.php
- Admin view: banka havale onay paneli
- Cron: aylık fatura kesimi
```

---

## 💬 OTURUM 7: Özel Teklif & Mesajlaşma (4-6 saat)

### Komut 7.1 — Özel Teklif Akışı
```
PRD bölüm 8.5 Flow B'deki özel teklif sistemi.

Yapılacaklar:
- /teklif-al — talep formu
  - Etkinlik tarihi, kişi sayısı
  - Lokasyon (harita seç)
  - Bütçe aralığı
  - Diyet gereksinimleri (helal, vegan, glutensiz vb.)
  - Hizmet türü: teslimat / self-servis / full-servis
  - Detay açıklama (max 1000 char)
  - Üyelik zorunlu (giriş yoksa kayıt formuna yönlendir)
- Talep kaydedilir, sistem eşleştirir:
  - Hizmet bölgesinde olan
  - Müsaitlik takvimi açık olan
  - Bütçe aralığında menüsü olan
  - Onaylı + aktif tedarikçiler
- Maks 10 tedarikçiye bildirim (e-posta + SMS + panel)
- 24 saat süre, dolduğunda talep kapanır
- Müşteri panelden gelen teklifleri karşılaştırır
- Birini seç → sipariş oluşur (Flow A'ya bağlanır)

Çıktı:
- Controller: QuoteRequestController.php (müşteri tarafı)
- Controller: Supplier/QuoteController.php (tedarikçi teklif verme)
- Service: QuoteMatchingService.php (eşleştirme algoritması)
- Service: QuoteScoringService.php (teklif karşılaştırma puanlama)
- View: quote-request/* + supplier/quotes/*
- Cron: süresi dolan talepleri kapat
```

### Komut 7.2 — Platform İçi Mesajlaşma
```
PRD bölüm 8.1/8.2/8.3'teki mesaj sistemi.

Yapılacaklar:
- Sipariş veya teklif talebi context'inde mesajlaşma
- /hesabim/mesajlar (müşteri), /tedarikci/mesajlar (tedarikçi)
- Konuşma listesi + detay görünümü
- Yeni mesaj geldiğinde:
  - In-app notification (header'da rozet)
  - E-posta (eğer 5 dk içinde okunmadıysa)
- Dosya eki (PDF, görsel max 5MB)
- "Okundu" rozet (read_at timestamp)
- Spam koruma: aynı kişiye 1 dk içinde max 3 mesaj

Yasak içerik (otomatik moderasyon):
- Telefon numarası (regex)
- E-posta adresi
- Dış link (https://, http://)
→ Tespit edilince mesaj bloke + admin bildirim

(Amaç: tarafların platform dışına çıkmasını engellemek)

Çıktı:
- Controller: MessageController.php
- Service: MessageService.php (moderasyon dahil)
- Service: NotificationService.php (e-posta, SMS, in-app)
- View: messages/inbox.php, messages/show.php
- JS: AJAX yeni mesaj polling (15 saniye)
```

---

## ⭐ OTURUM 8: Yorum, Bildirim, Lansman (3-5 saat)

### Komut 8.1 — Yorum Sistemi
```
PRD bölüm 3.1'deki yıldız + yorum sistemi.

Yapılacaklar:
- Sipariş status='delivered' olduktan 24 saat sonra:
  - Müşteriye e-posta + SMS: "Yemekhanecinizi değerlendirin"
  - 7 gün içinde yapılmazsa hatırlatma
- Yorum formu:
  - Genel puan (1-5 yıldız)
  - Yemek puanı, hizmet, dakiklik (1-5)
  - Yorum metni (min 20 char, max 1000)
  - Foto yükleme opsiyonel (max 3)
- Tedarikçi yanıt verebilir (1 kez, max 500 char)
- Admin moderasyonu: küfür, spam, taraflı yorum işaretleme

Hesaplamalar:
- Tedarikçi rating_avg = ortalama
- Otomatik tetikleyici: yorum eklendiğinde update

Görüntüleme:
- Tedarikçi sayfasında: en yeni 10, "Tümünü gör" sayfalama
- Filtre: yıldız sayısı, sadece fotolu
- Sıralama: en yeni, en yardımcı (faydalı oy)

Çıktı:
- Controller: ReviewController.php
- Service: ReviewModerationService.php
- View: reviews/* (form, listing)
- Migration: reviews tablosu (PRD'de zaten var, gerekirse ek alan)
- Cron: 24 saat sonra hatırlatma e-postası
```

### Komut 8.2 — Bildirim Sistemi (Merkezi)
```
PRD bölüm 3.1'deki bildirim altyapısı.

Yapılacaklar:
- NotificationService merkezi servis:
  - send($userId, $type, $data, $channels=['email','sms','in_app'])
- Driver pattern:
  - EmailDriver (Postmark veya Sendgrid)
  - SmsDriver (Netgsm veya İletimerkezi)
  - InAppDriver (DB notifications tablosu)
- Bildirim tipleri (template):
  - new_order_received (tedarikçi)
  - order_accepted (müşteri)
  - new_message (her iki taraf)
  - new_quote (müşteri)
  - quote_accepted (tedarikçi)
  - delivery_reminder (1 gün önce)
  - review_request (24 saat sonra)
  - payment_received (tedarikçi)
  - account_approved (tedarikçi)
- Kullanıcı tercihleri: hangi bildirim hangi kanaldan (settings sayfası)

Performans:
- Async kuyruğa al (basit dosya tabanlı queue veya Redis)
- Worker: php cli queue:work
- Hata durumunda 3 retry, sonra dead letter

Çıktı:
- Service: NotificationService.php
- Drivers: EmailDriver, SmsDriver, InAppDriver
- Templates: resources/views/emails/* (HTML + text)
- Settings: /hesabim/bildirim-tercihleri
- Cron worker setup
```

### Komut 8.3 — MVP Lansmana Hazırlık
```
Faz 5 sonu - lansman öncesi sıkılaştırma.

Kontrol listesi:
- [ ] Tüm sayfalarda CSRF token
- [ ] Tüm form'larda server-side validation
- [ ] Lighthouse skoru > 85 (Performance, SEO, Accessibility)
- [ ] Mobile test: 360px, 768px, 1024px
- [ ] KVKK aydınlatma metni + çerez izni banner
- [ ] Hata sayfaları (404, 500) - Türkçe, kullanıcı dostu
- [ ] Sitemap.xml + robots.txt
- [ ] Google Analytics 4 + Hotjar setup
- [ ] Google Search Console doğrulama
- [ ] SSL test (ssllabs.com → A+ olmalı)
- [ ] Backup test (DB restore tatbikatı)
- [ ] Admin panel 2FA aktif
- [ ] Rate limiting tüm kritik endpointlerde
- [ ] Log monitoring (en az son 7 gün retention)

Yapılacaklar:
- Eksik olanları tamamla
- staging.yemekhaneci.com.tr ile prod paralel test
- 5 pilot tedarikçi onboarding (UYSA ağından)
- 10 test sipariş (ödeme dahil)
- Bug ve feedback toplama formu

Çıktı:
- Pre-launch checklist (markdown)
- Smoke test scripti (curl + assertion)
- Tutorial video (tedarikçi onboarding)
```

---

## 📊 İlerleme Takibi

Her oturum sonunda:
1. **Çalışan demoyu kaydet** (screen recording)
2. **PROGRESS.md** dosyası güncelle (ne yapıldı, ne kaldı)
3. **DECISIONS.md** dosyası güncelle (önemli teknik kararlar + gerekçe)
4. **Issue tracker** (GitHub Issues veya basit todo.md): bulunan buglar
5. **Faz sonu retrospektif:** ne iyi gitti, ne zorladı, sonraki fazda ne değişmeli

---

## ⚠️ Acil Durum Komutları

### "Hatalı kod yayına gitti" - Rollback
```
Son commit'i geri al, staging'i prod'a kopyala.
git revert HEAD --no-edit
git push origin main
ssh deploy@vps "cd /var/www/yemekhaneci && git pull && php cli migrate"
```

### "Sunucu yavaşladı" - Quick Diagnostic
```
htop, iostat, mysqltuner, slow query log incele.
docker stats (eğer docker varsa).
Redis cache hit rate kontrol.
```

### "DB bozuldu" - Restore
```
Backblaze B2'den son backup indir, mysqldump restore et.
30 günlük retention sayesinde her zaman son 30 günün backup'ı var.
```

---

**Hatırlatma:**
- Her komutun başında `PRD.md ve CLAUDE.md'yi oku` cümlesi olsun
- Belirsiz bir şey görünce `Sor, varsayım yapma` cümlesini ekle
- Büyük modüllerde `Tek seferde değil, parça parça yap` cümlesini ekle

---

**Hazırlayan:** Claude (planlama) + Ömer (vizyon)
**Tarih:** 7 Mayıs 2026
**Dosya:** CLAUDE_CODE_KOMUTLARI.md

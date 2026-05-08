# 🚀 Yemekhaneci.com.tr — Claude Code İlk Komut Paketi

> **Bu dosya nasıl kullanılır?**
> 1. Claude Code'u açın (terminalde `claude code` komutu)
> 2. Aşağıdaki "BAŞLATMA OTURUMU" komutunu kopyalayıp yapıştırın
> 3. Claude'un yapacaklarını tek tek onaylayın
> 4. Bir oturum tamamlandığında bir sonrakine geçin
> 5. Her oturumun sonunda PROGRESS.md güncelleyin

---

## 📦 ÖN HAZIRLIK (Komut Çalıştırmadan Önce)

Aşağıdakilerin elinizde olduğundan emin olun:

- [x] **PRD v3.0** dosyası (`Yemekhaneci_PRD_v3.0.docx`)
- [x] **CLAUDE.md** dosyası (kod kuralları)
- [x] **Hostinger VPS** erişim bilgileri (IP, SSH key, root parola)
- [x] **Domain DNS yönetim paneli** erişimi (yemekhaneci.com.tr)
- [x] **GitHub** repo planı (private repo açılmış mı?)
- [x] **Geliştirme ortamı**: Claude Code kurulu, Git Bash, VSCode hazır

> **Önemli:** Çalışmaya başlamadan önce PRD ve CLAUDE.md dosyalarını proje kökünüze kopyalayın. Claude Code bunları okuyacak.

---

## 🎯 OTURUM 0: Başlatma ve Proje Kurulumu

**Bu oturum ne yapar?** Çalışma ortamını hazırlar, repo açar, klasör yapısını kurar.

### Komut Paketi:

```
[Faz 0 — Proje Başlatma]

Bağlam: Yemekhaneci.com.tr platformunu inşa ediyoruz. PRD.md (en önemli) ve
CLAUDE.md (kod kuralları) dosyalarını dikkatlice oku.

Görev:
1. Proje kök klasöründe Git repo başlat (henüz değilse)
2. PRD bölüm 14.2'deki klasör yapısını oluştur (boş dosyalarla)
3. .gitignore dosyası oluştur (PHP/Laravel + Node + .env + uploads)
4. README.md oluştur (proje tanımı, kurulum, klasör yapısı açıklaması)
5. PROGRESS.md oluştur (boş olarak; her faz sonunda güncelleyeceğiz)
6. DECISIONS.md oluştur (mimari kararlar için)
7. .env.example oluştur (PRD bölüm 14.1'deki tüm gerekli env değişkenleri ile)

Çıktı:
- Proje klasör ağacı
- README, PROGRESS, DECISIONS dosyalarının içerikleri
- Git ilk commit komutları (manuel çalıştıracağım)

Kurallar:
- Composer veya npm install çalıştırma (sadece dosya oluştur)
- Hiçbir kütüphane indirme, sadece package.json/composer.json'a yaz
- Bittiğinde "Hangi adımı şimdi yapayım?" diye sor
```

### Beklenen Çıktı:
- Klasör ağacı: `app/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `tests/`
- Boş dosyalar: README.md, PROGRESS.md, DECISIONS.md, .gitignore, .env.example
- composer.json (PHP bağımlılıkları taslağı)
- package.json (frontend build için)

---

## 🛠️ OTURUM 1: VPS Hazırlığı (Hostinger)

**Bu oturum ne yapar?** Hostinger VPS üzerinde Linux altyapısını kurar.

### Komut Paketi:

```
[Faz 0.1 — VPS Setup]

Bağlam: PRD bölüm 14.1'deki stack'e göre Hostinger VPS hazırlığı yapacağız.
Sunucu: Ubuntu 22.04 LTS, 4GB RAM, 80GB SSD.

Görev: VPS üzerinde çalışacak bir bash kurulum scripti yaz. Bu script şunu yapar:

1. Sistem güncellemesi (apt update && upgrade)
2. PHP 8.2 kurulumu (FPM + gerekli extensionlar: mbstring, xml, mysql, gd, imagick, intl, zip, curl)
3. MySQL 8 kurulumu + güvenlik (mysql_secure_installation otomasyonu)
4. Composer kurulumu (global)
5. Node.js 20 LTS kurulumu (nvm üzerinden)
6. Redis kurulumu
7. Traefik kurulumu (Docker üzerinden, otomatik SSL ile)
8. Nginx kurulumu (Traefik arkasında, dahili 80 portunda)
9. Git kurulumu
10. UFW firewall (sadece 22, 80, 443 açık)
11. fail2ban kurulumu (SSH brute force koruma)
12. Otomatik MySQL backup cron (günlük, /var/backups)
13. Imagick + WebP destekli image processing
14. Sertifika otomatik yenileme (certbot timer)
15. Geliştirici user oluştur (sudo + ssh key)

Çıktı:
- /opt/yemekhaneci/setup-vps.sh dosyası
- README'de "VPS kurulum adımları" bölümü
- Manuel çalıştırılacak komutlar listesi (SSH ile)

Kurallar:
- Script idempotent olsun (yeniden çalıştırılabilir)
- Her major adımda echo ile log bas
- Hata olunca exit 1 yapma — hatayı raporla, devam et
- .env değerlerini henüz set etme (gerçek prod'da elle set edilecek)
- ÖNEMLİ: Bu script local'de yazılır, ben SSH ile sunucuya kopyalayıp çalıştıracağım

Sonra: "Script hazır. SSH ile sunucuya kopyalayın ve çalıştırın. Bittiğinde
sonraki adıma geçeceğiz." de.
```

### Manuel Çalıştırma Adımları:

Script Claude Code tarafından üretildikten sonra:

```bash
# 1. Local'de scripti gözden geçir
cat setup-vps.sh

# 2. Hostinger VPS'ye SSH bağlan
ssh root@SUNUCU_IP

# 3. Scripti yükle (local'den)
scp setup-vps.sh root@SUNUCU_IP:/opt/

# 4. Sunucuda çalıştır
chmod +x /opt/setup-vps.sh
/opt/setup-vps.sh 2>&1 | tee /var/log/yemekhaneci-setup.log
```

---

## 🌐 OTURUM 2: Domain ve DNS Konfigürasyonu

**Bu oturum ne yapar?** yemekhaneci.com.tr domain'ini sunucuya bağlar, Traefik routes'u kurar.

### Komut Paketi:

```
[Faz 0.2 — Domain ve SSL]

Bağlam: Domain yemekhaneci.com.tr alındı. DNS yönetim paneli hazır.

Görev:
1. Hostinger DNS panelinde yapılacak A record listesi:
   - @ (boş) → SUNUCU_IP
   - www → SUNUCU_IP
   - api → SUNUCU_IP (gelecekte API için)
   - admin → SUNUCU_IP (admin paneli)
   Tablo formatında çıkar.

2. Traefik konfigürasyonu (docker-compose.yml + traefik.yml):
   - HTTP → HTTPS yönlendirmesi
   - Let's Encrypt otomatik SSL
   - yemekhaneci.com.tr → Nginx (HTTP)
   - www.yemekhaneci.com.tr → ana domain'e yönlendir
   - admin.yemekhaneci.com.tr → ileride yönlendirilecek

3. Nginx server block (sites-available/yemekhaneci):
   - PHP-FPM ile entegrasyon
   - Static asset caching (1 yıl)
   - Gzip compression
   - Security headers (X-Frame-Options, CSP, vs.)

4. SSL test komutu (sslLabs.com curl)

Çıktı:
- DNS kayıtları tablosu
- /opt/yemekhaneci/docker-compose.yml
- /opt/yemekhaneci/traefik.yml
- /etc/nginx/sites-available/yemekhaneci
- Manuel adımlar: DNS kayıtlarını ekle, SSL bekle, doğrula
```

---

## 🗄️ OTURUM 3: Veritabanı ve Migration'lar

**Bu oturum ne yapar?** PRD'deki 24 tabloyu MySQL'de oluşturur.

### Komut Paketi:

```
[Faz 1.1 — DB Migration]

Bağlam: PRD bölüm 13'teki 24 tabloyu MySQL'de oluşturacağız.
Charset: utf8mb4_turkish_ci (Türkçe karakter için kritik).

Görev: Laravel migration dosyaları oluştur (24 adet).

Sıra (dependency'ye göre):
1. categories (parent self FK)
2. users
3. corporate_profiles (FK users)
4. supplier_profiles (FK users)
5. supplier_documents (FK supplier_profiles)
6. supplier_service_areas (FK supplier_profiles)
7. supplier_cost_matrix (FK supplier_profiles)
8. supplier_personnel_costs (FK supplier_profiles)
9. supplier_personnel_template (FK supplier_profiles)
10. supplier_fixed_costs (FK supplier_profiles)
11. supplier_kitchen_equipment (FK supplier_profiles)
12. supplier_profit_margin (FK supplier_profiles)
13. supplier_menu_template (FK supplier_profiles)
14. menus (FK supplier_profiles, categories)
15. menu_items (FK menus)
16. menu_images (FK menus)
17. proactive_packages (FK supplier_profiles)
18. leads (standalone)
19. quote_requests (FK users, categories)
20. quotes (FK quote_requests, supplier_profiles)
21. b2b_listings (FK supplier_profiles)
22. orders (FK users, supplier_profiles)
23. messages (FK users)
24. reviews, payments, notifications, favorites, activity_log

Her migration dosyasında:
- create + drop tabloları
- indeks tanımları (PRD bölüm 13.X)
- foreign key constraints
- DECIMAL alanlar para için (10,2)
- ENUM alanlar enum() ile
- JSON alanlar json() ile
- created_at, updated_at, soft delete (gerekenler için)

Çıktı:
- database/migrations/ altında 24 dosya
- Her birini tek tek oluştur, atomic commit'lenebilir
- DB_RESET.md dokümanı: tüm migration'ı sıfırlayıp yeniden çalıştırma rehberi

Test:
- php artisan migrate:fresh komutu hatasız çalışmalı
- Her tablo doğru charset/collation olmalı
- Foreign key'ler doğru bağlanmalı
```

---

## 🔐 OTURUM 4: Kimlik Doğrulama (Auth)

**Bu oturum ne yapar?** Üç kullanıcı tipi için kayıt/giriş sistemi.

### Komut Paketi:

```
[Faz 1.2 — Auth Sistemi]

Bağlam: PRD bölüm 3.1'deki 3 kullanıcı tipi (admin, supplier, customer).
PRD bölüm 14.3'teki güvenlik kuralları zorunlu.

Görev:
1. User modeli (Laravel Eloquent veya saf PHP)
   - user_type enum kontrolü
   - Argon2id password hash
   - 2FA TOTP desteği (Google Authenticator)

2. Kayıt formları:
   - /kayit/musteri (B2C bireysel)
   - /kayit/kurumsal (B2B kurumsal — vergi no zorunlu)
   - /yemekci-ol (yemekçi başvuru — belge zorunlu)

3. Giriş:
   - /giris (tek form, user_type'a göre yönlendir)
   - Rate limit: 5 deneme / 15 dakika (Redis)
   - Captcha (3. başarısız girişten sonra)

4. Şifre sıfırlama:
   - /sifre-sifirla
   - E-posta token, 1 saat geçerli
   - PostMark/SendGrid entegrasyonu

5. E-posta doğrulama:
   - Kayıt sonrası otomatik e-posta
   - Link tıklayınca aktive

6. Telefon doğrulama:
   - SMS ile 6 haneli kod
   - Netgsm entegrasyonu (mock'la başla)

Test:
- Her form için unit test
- Rate limit test
- Şifre güçlüğü kuralları
- CSRF kontrolü
```

---

## 📊 OTURUM 5+ Devam Edecek Modüller

PRD'deki sıralamaya göre sonraki oturumlar:

```
Oturum 6: Yemekçi profil ve belgeler
Oturum 7: Maliyet matrisi 6 sekme (yemekçi paneli)
Oturum 8: Müşteri wizard'ı (9 soru)
Oturum 9: Fiyat motoru ve anonim yemekçi listesi
Oturum 10: Mail talep sistemi (lead capture)
Oturum 11: Menü kataloğu CRUD
Oturum 12: Sepet ve ödeme (iyzico + havale)
Oturum 13: Sipariş yönetim akışı
Oturum 14: Mesajlaşma ve yorum
Oturum 15: Admin panel modülleri
Oturum 16: KVKK formları, çerez banner, yasal sayfalar
Oturum 17: Reaktif teklif sistemi
Oturum 18: Lansmana hazırlık (Lighthouse, GA, Sentry)
```

Her oturum başlamadan, ilgili PRD bölümünü tekrar Claude Code'a hatırlat.

---

## 📝 ÖNEMLİ HATIRLATMALAR

### Her Oturum Sonunda:
1. PROGRESS.md güncelle (ne yapıldı, ne kaldı)
2. Git commit yap (anlamlı mesajla: "Faz 1.1: DB migration tamamlandı")
3. Staging sunucuya deploy yap (gerekiyorsa)
4. Test komutlarını çalıştır

### Sorunla Karşılaştığında:
1. Claude Code'a "şu hatayı alıyorum" diye yapıştır
2. PRD'de ilgili bölümü tekrar göster
3. CLAUDE.md kurallarını hatırlat
4. Hata düzelene kadar devam etme

### Yasak Davranışlar (CLAUDE.md'den):
- ❌ "Bonus olarak şunu da ekledim" → SADECE istenen yapılır
- ❌ "Daha iyi olur diye değiştirdim" → DEĞİŞTİRMEK İÇİN ÖNCE SOR
- ❌ Composer/npm paket eklemek (sormadan)
- ❌ Migration ile veri silme (sormadan)
- ❌ .env dosyasını commit'lemek
- ❌ Direkt SQL — sadece migration üzerinden

### Beklenen Davranışlar:
- ✅ Belirsizse "Bunu şöyle anlıyorum, doğru mu?" diye sorar
- ✅ Hata varsa root cause'u bulur, üstünü örtmez
- ✅ Her dosyada en üste 1-2 satır docblock
- ✅ TODO'ları açık yazar
- ✅ Her faz commit'lenir, staging'e deploy

---

## 🎯 İLK HAFTAKİ HEDEF

Bu hafta sonu itibariyle bitmesi gerekenler:

- [x] PRD okundu ve onaylandı
- [x] Destekleyici dokümanlar tamamlandı (marka brief, sözleşmeler, onboarding)
- [ ] Hostinger VPS hazırlandı (Oturum 0 + 1)
- [ ] Domain DNS bağlandı, SSL aktif (Oturum 2)
- [ ] DB migration tamamlandı (Oturum 3)
- [ ] Auth sistemi çalışır durumda (Oturum 4)

**5-7 gün içinde** boş bir "yemekhaneci.com.tr" sitesi canlıda olmalı, kullanıcı kayıt/giriş çalışmalı.

---

## 💡 İPUCU

Claude Code ile çalışırken her oturumda **net bir başarı kriteri** belirleyin. Örneğin:

> "Bu oturum, `php artisan migrate:fresh` komutu hatasız çalışana kadar bitmeyecek."

Bu Claude'u odaklı tutar, gereksiz iyileştirmelere takılmaz.

---

**Hazır mısınız?** Oturum 0 ile başlayalım. Komutu kopyalayıp Claude Code'a yapıştırın ve süreci başlatın. Sorunla karşılaşırsanız, mevcut Claude'a (bu sohbet) gelin, çözüm bulalım.

---

*Doküman versiyonu: 1.0*
*Son güncelleme: 8 Mayıs 2026*
*Hazırlayan: UYSA & Claude AI*

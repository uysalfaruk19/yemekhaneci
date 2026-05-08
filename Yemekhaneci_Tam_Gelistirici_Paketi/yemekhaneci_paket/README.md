# 🍽️ Yemekhaneci.com.tr — Geliştirici Başlangıç Paketi

> **Türkiye'nin Tarafsız Catering Pazaryeri**

Bu paket, Yemekhaneci.com.tr platformunun sıfırdan inşası için gereken **tüm dokümantasyonu, demo kodları ve geliştirme rehberlerini** içerir. Claude Code ile çalışırken **tek doğru kaynak**tır.

---

## 📦 Paket İçeriği

```
yemekhaneci_paket/
│
├── README.md                         ← BU DOSYA — başlangıç noktası
│
├── 01_PRD/                           ← Ürün gereksinimleri
│   ├── PRD.md                        (Markdown — Claude Code için optimize)
│   └── Yemekhaneci_PRD_v3.0.docx    (Word — okuma/yazdırma için)
│
├── 02_Kurallar/                      ← Geliştirme kuralları
│   ├── CLAUDE.md                     (kod kuralları, güvenlik, KVKK)
│   └── CLAUDE_CODE_KOMUTLARI.md      (8 oturumluk hazır komutlar)
│
├── 03_Demo_HTML/                     ← UI/UX davranış referansları
│   ├── 01_musteri_wizard.html        (9 soru wizard + anonim liste + mail)
│   ├── 02_yemekci_panel.html         (6 sekmeli maliyet paneli)
│   └── 03_admin_panel.html           (12 modüllü yönetim paneli)
│
├── 04_Claude_Code_Komutlar/          ← Adım adım başlangıç
│   └── 04_Claude_Code_Baslangic_Komutlari.md
│
├── 05_Hukuki/                        ← Sözleşme şablonları
│   └── 02_Hukuki_Sozlesme_Sablonlari.docx
│       (Yemekçi sözleşmesi + Müşteri kullanım koşulları
│        + KVKK aydınlatma + Çerez politikası)
│
├── 06_Marka/                         ← Marka kimliği brief
│   └── 01_Marka_Kimligi_Brief.docx   (logo + renk + tipografi spec'i)
│
├── 07_Pilot/                         ← Saha ekibi rehberi
│   └── 03_Pilot_Onboarding_Rehberi.docx
│       (UYSA ekibi için pilot yemekçi çekme rehberi)
│
└── 08_Veritabani/                    ← Database
    └── database_schema.sql           (28 tablo, çalıştırılabilir SQL)
```

**Toplam paket boyutu**: ~400 KB | **Dosya sayısı**: 11 | **Bilgi yoğunluğu**: Production'a hazır

---

## 🚀 Hızlı Başlangıç

### 1. Bu Paketi Açın
Dosyayı indirin, ZIP açın veya klonlayın. Tüm klasör yapısı korunmalı.

### 2. Önce Bu Sırayla Okuyun

| Sıra | Dosya | Süre | Amaç |
|------|-------|------|------|
| 1 | **`README.md`** (bu dosya) | 5 dk | Genel bakış |
| 2 | **`01_PRD/PRD.md`** | 45 dk | Tüm sistem özelliklerini öğren |
| 3 | **`02_Kurallar/CLAUDE.md`** | 15 dk | Kod yazım kuralları |
| 4 | **`03_Demo_HTML/`** içindeki 3 dosyayı tarayıcıda aç | 10 dk | Görsel referans |
| 5 | **`04_Claude_Code_Komutlar/04_Claude_Code_Baslangic_Komutlari.md`** | 20 dk | Adım adım başla |

### 3. Claude Code'u Açın

```bash
# Claude Code'u çalıştır
claude code

# İlk komut (Oturum 0)
# 04_Claude_Code_Komutlar/ klasöründeki komutu kopyala-yapıştır
```

### 4. Faz Faz İlerleyin

`04_Claude_Code_Baslangic_Komutlari.md` içinde 16 oturumluk yol haritası var. **Her oturum bitmeden bir sonrakine geçmeyin.**

---

## 📋 Proje Özeti (1 Sayfa)

### Vizyon
**Türkiye'nin tarafsız catering pazaryeri** — kurumsal ve bireysel müşterileri yemekçi firmalarla şeffaf, hızlı, kontrollü olarak buluşturur.

### Temel Fark
- **Tarafsız platform**: UYSA sahip ama sıradan bir yemekçi gibi katılır
- **Maliyet tabanlı şeffaflık**: Yemekçi kendi maliyetini girer, müşteri ortalama görür
- **Anonim eşleştirme**: Yemekçi isimleri gizli, müşteri mail bırakırsa açılır

### Üç Panel
1. **Admin** (`/yonetim`) — UYSA iç ekip, 12 modül
2. **Yemekçi** (`/yemekci`) — Onaylı catering firmaları, 6 sekmeli maliyet
3. **Müşteri** (`/hesabim`) — B2B + B2C kullanıcılar

### Tek Cümlede İş Modeli
Yemekçi platforma kayıt olur, maliyet matrisini doldurur. Müşteri anasayfada 9 soru cevaplar, **anonim yemekçi listesi + ortalama fiyat** görür. E-posta ile detaylı liste alır, doğrudan iletişim kurar. Sipariş platform üzerinden geçer, **%5-12 komisyon** kesilir.

### Teknik Stack
PHP 8.2 + MySQL 8 + Bootstrap 5 + Alpine.js + Hostinger VPS + Traefik + iyzico/PayTR

### Lansman Hedefi
**5-6 ay içinde MVP** — 100+ yemekçi, ₺3-5M aylık GMV

---

## 🎯 Kritik Bilgiler (Claude Code'a İletilmesi Gerekenler)

Bu bilgileri Claude Code oturumunun başında **mutlaka** belirtin:

### 1. Kullanılan Domain
```
yemekhaneci.com.tr (alındı, aktif)
```

### 2. Renkler (UI'de kullanılacak)
```css
--brand-primary: #6B1F2A;  /* derin bordo */
--brand-accent:  #C9A961;  /* eski altın */
--brand-cream:   #FAF6F0;  /* krem zemin */
```

### 3. Fontlar
- Başlık: **Cormorant Garamond** (Google Fonts)
- Gövde: **Manrope** (Google Fonts)
- Sayısal: **JetBrains Mono** (Google Fonts)

### 4. Türkçe Karakter Desteği
**ZORUNLU**: Tüm DB tabloları `utf8mb4_turkish_ci` collation kullanmalı.

### 5. Komisyon Yapısı
- Direkt sipariş: %8-12
- Reaktif teklif: %5-7
- Proaktif paket: %8-12
- B2B tedarik: %3-5
- **İlk 6 ay sıfır komisyon** (pilot kampanya)

---

## 🛠️ Claude Code İlk Komut (Kopyala-Yapıştır)

```
[Faz 0 — Proje Başlatma]

Bağlam: Yemekhaneci.com.tr platformunu inşa ediyoruz.
Lütfen bu dosyaları sırayla oku:
1. README.md (bu dosya)
2. 01_PRD/PRD.md (tam ürün spec)
3. 02_Kurallar/CLAUDE.md (kod kuralları)
4. 08_Veritabani/database_schema.sql (DB yapısı)
5. 03_Demo_HTML/ klasöründeki 3 HTML (UI referansı)

Görev:
1. Proje kök klasöründe Git repo başlat
2. PRD bölüm 14.2'deki klasör yapısını oluştur (boş dosyalarla)
3. .gitignore (PHP/Laravel + Node + .env + uploads)
4. README.md (proje tanımı, kurulum, klasör yapısı)
5. PROGRESS.md (her faz sonunda güncellenir)
6. DECISIONS.md (mimari kararlar)
7. .env.example (PRD bölüm 14.1'deki tüm env değişkenleri)
8. composer.json (PHP bağımlılıkları taslağı, install yapma)
9. package.json (frontend build, install yapma)

Kurallar:
- Composer veya npm install ÇALIŞTIRMA (sadece dosya oluştur)
- Hiçbir kütüphane indirme, sadece package.json/composer.json'a yaz
- Bittiğinde "Hangi adımı şimdi yapayım?" diye sor
- Demo HTML'leri açıp UI tarzını anla, sonra kodlama yapacağız
```

---

## 📐 Geliştirme Yol Haritası (Özet)

| Faz | Süre | Çıktı |
|-----|------|-------|
| **Faz 0**: Setup | 1 hafta | VPS, repo, klasörler, DNS, SSL |
| **Faz 1**: Temel | 3 hafta | DB migration, Auth (3 tip), yemekçi onay |
| **Faz 2**: Maliyet | 3 hafta | Yemekçi panel 6 sekme + canlı önizleme |
| **Faz 3**: Wizard | 3 hafta | 9 soru anasayfa, fiyat motoru, anonim liste |
| **Faz 4**: Mail | 2 hafta | Lead capture, mail sistemi, KVKK |
| **Faz 5**: Menü | 2 hafta | Menü kataloğu, müşteri tarama |
| **Faz 6**: Sipariş | 3 hafta | Sepet, ödeme, sipariş yönetim |
| **Faz 7**: Teklif | 2 hafta | Reaktif teklif, mesajlaşma, yorum |
| **Faz 8**: Beta | 3 hafta | Pilot test, hata düzeltme |
| **Faz 9**: Lansman | Sürekli | PR, pazarlama, büyüme |

**Toplam: ~22 hafta MVP, ~26 hafta açık lansman**

---

## ⚠️ Yapmamanız Gerekenler

Claude Code ile çalışırken sıkça yapılan **yanlışlar**:

### ❌ Yapma:
- Tüm fazları paralel başlatma (sıralı git)
- Demo HTML'leri "production kod" sanma (sadece referans)
- PRD'yi okumadan kodlamaya başlama
- Tek mega-prompt ile tüm sistemi yazdırmaya çalışma
- `.env` dosyasını commit etme
- Sözleşmeleri avukata gitmeden production'a alma

### ✅ Yap:
- Her oturumda PROGRESS.md güncelle
- Hata olunca devam etme, root cause'u bul
- Belirsizlikte Claude Code'a "doğru anlıyor muyum?" diye sorma şansı ver
- Her faz sonunda staging'e deploy yap
- Kod commit'lerin anlamlı olsun: "Faz 1.1: DB migration tamamlandı"

---

## 🔗 Önemli Bağlantılar

### Erişim Bilgileri (Doldurulacak)
- **Domain**: yemekhaneci.com.tr — DNS yönetimi: [Hostinger paneli]
- **VPS**: [IP adresi] — SSH: `ssh root@IP`
- **Git repo**: [GitHub URL] (private)
- **Staging**: staging.yemekhaneci.com.tr (Faz 1 sonrası)
- **Production**: yemekhaneci.com.tr (lansman sonrası)

### Üçüncü Taraf Hizmetler (Başvurulacak)
- **iyzico**: https://merchant.iyzipay.com (kart ödeme)
- **PayTR**: https://www.paytr.com (taksit/B2B)
- **PostMark**: https://postmarkapp.com (transactional mail)
- **SendGrid**: https://sendgrid.com (alternatif mail)
- **Netgsm**: https://netgsm.com.tr (SMS)
- **Cloudflare**: https://cloudflare.com (CDN + DNS)
- **Sentry**: https://sentry.io (hata takibi)
- **Hotjar**: https://hotjar.com (UX analitik)

### Yasal Süreçler
- **VERBİS başvurusu**: https://verbis.kvkk.gov.tr
- **MERSİS sorgu**: https://mersis.gtb.gov.tr
- **E-Fatura entegratör**: ParamPos veya Foriba

---

## 📞 İletişim ve Destek

### UYSA Ekibi
- **Süper Admin**: Ömer M. (final karar)
- **Operasyon**: Emrullah Gökhan
- **Operasyon**: Emre Köse

### Hizmet Sağlayıcılar (Lansman İçin)
- **Avukat**: [Bilişim/ticaret hukuku uzmanı] — sözleşme review
- **Mali Müşavir**: [Mevcut mali müşavir] — e-fatura entegrasyonu
- **Tasarımcı**: [Freelance veya ajans] — logo + marka kimliği
- **Fotoğrafçı**: [Yerli profesyonel] — yemekçi mutfak çekimleri

---

## 🎓 Bilmeniz Gerekenler

### Bu Paket Nasıl Üretildi?
Bu paket, **Anthropic Claude (AI)** ile **Ömer M. (UYSA)** arasındaki uzun bir tasarım sürecinin sonucudur. Yaklaşık **30+ saatlik** sohbet, 5+ iterasyon ve gerçek UYSA verileriyle (Mayıs 2026 menü) test sonucu oluşmuştur.

### Doküman Versiyonu
- **PRD**: v3.0 (production spec)
- **Demo HTML**: v3.0 (anonim liste + mail sistemi dahil)
- **Database Schema**: v1.0
- **CLAUDE.md**: v1.0
- **Bu README**: v1.0

### Lisans
Bu doküman ve içeriği **UYSA Yemek Hizmetleri**'ne aittir. İzinsiz kopyalanamaz, dağıtılamaz.

### Sürüm Notları
- **v3.0** (Mayıs 2026): Anonim liste + mail sistemi eklendi, menü şablonu yapısı dahil edildi
- **v2.0** (Mayıs 2026): 6 sekmeli maliyet motoru, 12 modüllü admin paneli
- **v1.0** (Mayıs 2026): İlk sürüm — temel 3 panel mimarisi

---

## ✅ Önümüzdeki 30 Günlük Aksiyon Planı

### Hafta 1: Hazırlık
- [ ] Bu paketi UYSA ekibiyle birlikte oku
- [ ] Hukuki sözleşmeleri avukata gönder (Hafta 1 başı)
- [ ] Marka brief'i 2-3 tasarımcıya gönder, teklif al
- [ ] VERBİS başvurusunu başlat (30+ gün sürer)
- [ ] Hostinger VPS satın al (4GB RAM yeterli)

### Hafta 2-3: Kurulum
- [ ] Claude Code Oturum 0-2 (proje, VPS, DNS+SSL)
- [ ] iyzico merchant başvurusu (paralel)
- [ ] PayTR merchant başvurusu (paralel)
- [ ] Pilot yemekçi listesi hazırla (30-40 firma)

### Hafta 4: Temel Geliştirme
- [ ] Claude Code Oturum 3-4 (DB migration, Auth)
- [ ] Logo + marka kimliği teslim alındı
- [ ] İlk pilot yemekçilerle ilk telefon görüşmeleri
- [ ] Avukat review tamamlandı, sözleşme v2.0

---

## 🌟 Başarı Garantisi

Bu paket size **garanti edilen şeyler**:

✅ **Eksiksiz spec**: Hiçbir kritik özellik eksik bırakılmamış
✅ **Production-ready**: Demo HTML değil, gerçek üretime hazır PHP/MySQL şema
✅ **Yasal hazırlık**: KVKK, TKHK, e-fatura uyumu için yapılacaklar listesi
✅ **Pazara giriş stratejisi**: Pilot programdan sosyal kanıta kadar
✅ **5-6 ay içinde MVP**: Net yol haritası ile

---

## 🚀 Hadi Başlayalım!

```bash
cd yemekhaneci_paket/
cat README.md  # Burayı tekrar okuyun
cat 01_PRD/PRD.md  # Sonra bunu okuyun
cat 02_Kurallar/CLAUDE.md  # Sonra bunu

# Tarayıcıda demoları aç
open 03_Demo_HTML/01_musteri_wizard.html
open 03_Demo_HTML/02_yemekci_panel.html
open 03_Demo_HTML/03_admin_panel.html

# Claude Code'u aç
claude code

# İlk komutu yapıştır (yukarıda ⬆️)
```

**İyi şanslar Yemekhaneci.com.tr ekibi!** 🇹🇷🍽️

---

*© 2026 UYSA Yemek Hizmetleri — Yemekhaneci.com.tr*
*Türkiye'nin Yemekhanecisi*

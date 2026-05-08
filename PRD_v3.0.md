# Yemekhaneci.com.tr — Ürün Gereksinim Dokümanı (PRD)

**Versiyon:** 3.0 (Production Spec)
**Tarih:** Mayıs 2026
**Sahip:** UYSA Yemek Hizmetleri
**Hazırlayan:** Ömer M. & Claude AI

> Bu doküman, Claude Code geliştirme süreçlerinde **tek doğru kaynak** olarak kullanılır. Demo HTML dosyaları (`03_Demo_HTML/`) referans olarak incelenmelidir — UI/UX davranışlarının somut karşılığını gösterir.

---

## İçindekiler

1. [Yönetici Özeti](#1-yönetici-özeti)
2. [Vizyon ve Stratejik Konumlandırma](#2-vizyon-ve-stratejik-konumlandırma)
3. [Üç Kullanıcı Tipi ve Üç Panel](#3-üç-kullanıcı-tipi-ve-üç-panel)
4. [Yönetici Paneli (Admin)](#4-yönetici-paneli-admin)
5. [Yemekçi Paneli](#5-yemekçi-paneli)
6. [Müşteri Paneli](#6-müşteri-paneli)
7. [Anasayfa Wizard (9 Soru)](#7-anasayfa-fiyat-hesaplayıcı-9-soru)
8. [Maliyet Tabanlı Fiyatlandırma Motoru](#8-maliyet-tabanlı-fiyatlandırma-motoru)
9. [Menü Şablonu Yapısı](#9-menü-şablonu-yapısı)
10. [Anonim Liste ve Mail Sistemi](#10-anonim-liste-ve-mail-sistemi)
11. [Üç Teklif Türü](#11-üç-teklif-türü)
12. [Gelir Modeli](#12-gelir-modeli)
13. [Veritabanı Şeması](#13-veritabanı-şeması-24-tablo)
14. [Teknik Mimari](#14-teknik-mimari)
15. [Sayfa Haritası](#15-sayfa-haritası-ve-user-flow)
16. [UX/UI Tasarım Sistemi](#16-uxui-tasarım-sistemi)
17. [Sezgisellik ve Kolay Kullanım](#17-sezgisellik-ve-kolay-kullanım)
18. [Kurumsallık ve Güven](#18-kurumsallık-ve-güven)
19. [Faz Yol Haritası](#19-faz-yol-haritası)
20. [Roadmap ve Backlog](#20-roadmap-ve-backlog)
21. [Risk Analizi](#21-risk-analizi)
22. [Claude Code Disiplini](#22-claude-code-disiplini)
23. [Lansmana Hazırlık](#23-lansmana-hazırlık-checklist)

---

## 1. Yönetici Özeti

### 1.1 Proje Tek Cümlede

Yemekhaneci.com.tr, Türkiye'de catering ve toplu yemek sektörünün en kapsamlı, **tarafsız**, dijital pazaryeridir. Müşteriler 30 saniyede ortalama fiyatlarını öğrenir, yemekçiler kendi maliyetleri üzerinden teklif sunar, **anonim listeleme + mail tabanlı eşleştirme** ile gizlilik korunur.

### 1.2 Üç Kritik Farklılaşma

- **Tarafsız Platform**: UYSA platform sahibidir, kendi catering hizmetini bu platformdan satmaz. Yemekçiler "rakip platformuna" değil, "tarafsız pazaryerine" kaydolur.
- **Maliyet Tabanlı Şeffaf Fiyatlandırma**: Müşteri 9 soruluk wizard ile bölgesindeki ortalama fiyatları anında görür; yemekçiler kendi gerçek maliyet matrislerini panel üzerinden tanımlar.
- **Anonim Eşleştirme**: Müşteri yemekçi isimlerini görmeden fiyat karşılaştırması yapar, sadece e-posta ile detaylı listeyi alır. Yemekçi "platform dışı satışa kayar" riski azaltılır.

### 1.3 Pazar Büyüklüğü

Türkiye toplu yemek pazarı 2026 itibarıyla yaklaşık **100 milyar TL**, etkinlik catering ek 14 milyar TL. Dijitalleşme oranı %5'in altında, büyük boşluk var. 3 yıllık hedef: **%1 pazar penetrasyonu (~1.14 milyar TL GMV)**.

### 1.4 Domain ve Marka

- Ana domain: `yemekhaneci.com.tr` (alındı, aktif)
- Defansif portföy önerilen: yemekhaneci.tr, yemekhaneci.io, yemekhanecim.com
- Marka pozisyonu: **Türkiye'nin yemekhanecisi** — yerli, kurumsal, güvenilir

---

## 2. Vizyon ve Stratejik Konumlandırma

### 2.1 Vizyon

> "2030 yılına kadar Türkiye'deki her toplu yemek tedarik sürecinde, en az bir adım Yemekhaneci.com.tr üzerinden geçer."

### 2.2 Misyon

Catering hizmeti almak isteyen kurumsal ve bireysel müşterileri, bu hizmeti sunan yemekçi firmalarla **şeffaf, hızlı, kontrollü ve operasyonel olarak destekli** bir dijital platformda buluşturmak.

### 2.3 Stratejik Hedefler

| Yıl | Aktif Yemekçi | Aylık GMV | Aylık Sipariş |
|-----|---------------|-----------|---------------|
| Yıl 1 (2026) | 100+ | 3-5 milyon TL | 500-1.000 |
| Yıl 2 (2027) | 400+ | 20-30 milyon TL | 3.000-5.000 |
| Yıl 3 (2028) | 1.200+ | 80-100 milyon TL | 10.000+ |

### 2.4 Rekabet Analizi

- **Ana rakip**: yemekhanem.com.tr — pazaryeri, lead generation odaklı. Eksiği: yemekçilerin maliyet matrisi yok, anonim liste yok, operasyon araçları yok.
- **Yan rakipler**: Trendyol Yemek, Yemeksepeti — bireysel servis odaklı, B2B/toplu yemek yok.
- **Yemekhaneci'nin avantajları**: (1) Maliyet matrisli şeffaf fiyatlama, (2) Anonim eşleştirme + mail kontrol, (3) Operasyon SaaS bileşenleri (Faz 3), (4) UYSA'nın 20+ yıllık sektör deneyimi.

---

## 3. Üç Kullanıcı Tipi ve Üç Panel

| Tip | URL | Kim Girer | Erişim Düzeyi |
|-----|-----|-----------|---------------|
| **Yönetici (Admin)** | `/yonetim` | UYSA iç ekip — Süper Admin (Ömer), Operasyon Admin (Emrullah, Emre) | Tüm sistem — okuma/yazma/silme/audit |
| **Yemekçi** | `/yemekci` | Onaylı catering firmaları | Sadece kendi verileri — profil, menü, sipariş, maliyet |
| **Müşteri** | `/hesabim` | Kurumsal (B2B) + Bireysel (B2C) | Sadece kendi siparişleri, talepleri, mesajları |

### 3.1 Admin Rolleri (UYSA İç Ayrım)

- **Süper Admin**: Tüm modüllere erişim. Sadece Ömer.
- **Operasyon Admin**: Yemekçi onayları, sipariş takibi, anlaşmazlık. (Emrullah, Emre)
- **Finans Admin**: Sadece komisyon, ödeme, fatura modülleri.
- **Pazarlama Admin**: Kupon, kampanya, e-posta gönderimi, raporlar.
- **Müşteri Hizmetleri**: Kullanıcı destek, mesajlaşma, basit moderasyon.

### 3.2 Persona — Kurumsal Müşteri

- **Örnek**: Mehmet Bey, 47 yaşında, 250 kişilik fabrika İK Müdürü, Gebze.
- **İhtiyaç**: Aylık öğle yemeği kontratı, ISO sertifikalı tedarikçi, sabit fiyat, e-fatura.
- **Acı nokta**: 5-10 firmayla manuel teklif toplama, kalite tutarsızlığı, fiyat şeffafsızlığı.
- **Çözüm**: Wizard'da 9 soru → bölgesindeki ortalama fiyat → mail ile detaylı liste → en uygun 3-5 yemekçi ile direkt iletişim.

### 3.3 Persona — Bireysel Müşteri

- **Örnek**: Ayşe Hanım, 35 yaşında, oğlunun 60 kişilik mezuniyet partisi düzenliyor.
- **İhtiyaç**: Hızlı, görsel, fiyat şeffaf, online ödeme, güvenilir.
- **Çözüm**: Wizard hızlı doldurma, anonim liste karşılaştırma, mobil uyumlu UX.

### 3.4 Persona — Yemekçi

- **Örnek**: Ahmet Bey, 25 yıllık catering firması sahibi, günlük 800 öğün kapasiteli, Kocaeli.
- **İhtiyaç**: Yeni müşteri kazanımı, dijital varlık, operasyonel verimlilik, fatura takibi.
- **Acı nokta**: Pazarlama bütçesi yok, web sitesi zayıf, manuel iş takibi, "platforma kayıt rakibe açılır mı?" korkusu.
- **Çözüm**: Tarafsız platform, anonim liste (ismi sonradan açılır), maliyet matrisli adil fiyatlandırma, sıfır risk ilk 6 ay komisyon.

---

## 4. Yönetici Paneli (Admin)

> 📺 **Demo HTML referansı**: `03_Demo_HTML/03_admin_panel.html`

Tüm platformun kontrol merkezi. Sol tarafta sticky sidebar (240px), üstte topbar (search + notification + logout), ana alanda dinamik içerik. Mobil görünümde sidebar hamburger menü ile açılır.

### 4.1 Sidebar Navigation Yapısı

#### Genel
- 📊 Dashboard
- 📡 Canlı Aktivite

#### Operasyon
- ✅ Yemekçi Onayları (rozet: bekleyen sayı)
- 👥 Kullanıcılar
- 🍳 Yemekçiler
- 📦 Siparişler
- 💬 Teklifler
- ⚖️ Anlaşmazlıklar (rozet: açık vaka sayısı)

#### İçerik
- 🛡️ Moderasyon (rozet)
- 🍽️ Menüler ve Paketler
- 📂 Kategoriler

#### Finans
- 💰 Finansal Genel Bakış
- 📈 Komisyonlar
- 🏦 Yemekçi Ödemeleri
- 🧾 Faturalar

#### Pazarlama
- 🎟️ Kuponlar ve Kampanyalar
- 📧 Toplu Bildirim
- 📑 Raporlar

#### Sistem
- ⚙️ Sistem Ayarları
- 🔐 Admin Ekibi
- 🗂️ Audit Log

### 4.2 Dashboard

#### KPI Kartları (6 adet, üst sırada)
- Aylık GMV (TL) + trend (geçen aya göre %)
- Aktif Sipariş sayısı
- Komisyon Geliri
- Yeni Kullanıcı (son 30 gün)
- Aktif Yemekçi
- Ortalama Puan (sistem geneli)

#### Grafikler
- Aylık Satış Trendi (line chart, GMV + Komisyon)
- Kategori Dağılımı (donut chart: Toplu Yemek, Davet, Kahvaltı, Diğer)

#### Tablolar
- En Çok Sipariş Alan 5 Yemekçi (sipariş, GMV, komisyon, puan)
- Son Aktiviteler (activity feed, son 6-10 hareket)

### 4.3 Canlı Aktivite Akışı

Tüm sistem hareketleri gerçek zamanlı listelenir. WebSocket veya 5sn polling. Filtre: aktivite türü, zaman aralığı, kullanıcı adı.

**Aktivite Tipleri:**
- 📦 Sipariş kabul/red/teslim
- 👤 Yeni kullanıcı kaydı
- 💬 Teklif talebi/teklif gönderme
- ⭐ Yorum eklendi
- ✅ Yemekçi onayı
- ⚠️ Anlaşmazlık açıldı
- 💰 Komisyon eklendi
- 🚫 Spam/içerik moderasyonu

### 4.4 Yemekçi Onay Sistemi (KYC)

#### Liste sütunları:
- Firma adı + iletişim kişisi
- Vergi numarası
- Şehir
- Belgelerin tamlığı (5/5, 4/5 vb.)
- Başvuru tarihi
- Durum (İnceleme, Belge Eksik, Onaylı, Reddedildi)
- Aksiyonlar (Detay, Onayla, Reddet)

#### Detay sayfası:
- Tüm firma bilgileri (vergi levhası, ticari sicil, adres)
- Belge görüntüleyici (PDF/görsel inline preview)
- Aksiyonlar: Onayla / Reddet (sebep notu) / Ek Belge İste
- Otomatik bildirim: aksiyon sonrası yemekçiye e-posta + SMS

### 4.5 Kullanıcı Yönetimi

Tüm kullanıcılar (Kurumsal, Bireysel, Yemekçi, Admin) tek listede. Filtre: tip, durum, kayıt tarihi, son giriş.

**Aksiyonlar:**
- Detay görüntüleme (tüm bilgiler + aktivite geçmişi)
- Düzenleme (e-posta, telefon, durum)
- Askıya alma / yasaklama
- Şifre sıfırlama
- Toplu işlem: CSV export, toplu e-posta gönderimi
- KVKK: kullanıcı verisi silme talebi yönetimi

### 4.6 Sipariş ve Teklif Takibi

#### Filtre:
- Durum (Bekleyen, Kabul, Hazırlanıyor, Yolda, Teslim, İptal)
- Tarih aralığı, Yemekçi, Müşteri, Tutar aralığı

#### Detay sayfası:
- Tüm sipariş bilgileri
- Adımlar timeline (oluşturuldu → ödeme → kabul → hazırlık → teslim)
- Müşteri ↔ yemekçi mesaj akışı
- Manuel müdahale: durum değiştirme, iade başlatma, mesaj gönderme
- Komisyon hesabı detayı

### 4.7 Anlaşmazlık Yönetimi

#### KPI'lar:
- Açık Vaka sayısı
- Bu Ay Çözülen
- Ortalama Çözüm Süresi (gün)
- Müşteri Lehine % oranı

#### Vaka detayı:
- Tarafların yazışmaları
- Sipariş geçmişi
- Belge ve fotoğraf delilleri
- Karar verme: müşteri lehine iade / yemekçi lehine kapanış / kısmi iade
- Yemekçi tekrarlayan şikayetlerde otomatik askıya alma (3 negatif)

### 4.8 Finansal Yönetim

#### KPI'lar:
- Aylık Brüt Gelir (komisyonlar)
- Yemekçilere Ödenecek (havale bekleyenler)
- Bekleyen Tahsilat (sipariş ödemesi gelmemiş)
- Komisyon Havuzu (toplam birikim)

#### Raporlar:
- Aylık komisyon trendi (line chart)
- Komisyon kanal dağılımı (Direkt %65, Reaktif %24, Proaktif %9, B2B %2)
- Yemekçi alacak/borç bakiyeleri
- Haftalık ödeme talimatı CSV (banka transferi için)
- E-fatura entegrasyonu durumu

### 4.9 İçerik Moderasyonu

- Yeni eklenen menülerin admin onayı
- Yemek fotoğrafları AI içerik kontrolü + manuel onay
- Yorum moderasyonu (işaretlenenler listesi)
- Proaktif paket onayı (yemekçinin oluşturduğu özel paketler)
- Yasaklı kelime listesi yönetimi

### 4.10 Pazarlama Araçları

- Kupon yönetimi (yüzde, sabit tutar, kategori bazlı, tek kullanımlık)
- Kampanya yönetimi (tarih aralıklı, otomatik aktive)
- Toplu e-posta/SMS (segment bazlı)
- Banner yönetimi (anasayfa hero, kategori sayfası)
- Öne çıkarma (belirli yemekçi/menüleri ana sayfaya taşı)

### 4.11 Sistem Ayarları

- Komisyon oranları (kategori bazlı: Direkt %10, Reaktif %6, Proaktif %10, B2B %4)
- Kategori yönetimi (CRUD, parent/child)
- Şehir/ilçe yönetimi
- Ödeme sağlayıcı ayarları (iyzico, PayTR API anahtarları)
- E-posta ve SMS şablonları (Türkçe + değişkenler: `{{ad}}`, `{{tutar}}`)
- Genel ayarlar: site başlığı, sosyal medya, KVKK metni, hakkımızda
- Bakım modu (planlı kesintilerde aktive)

### 4.12 Audit Log (Denetim İzi)

Her admin aksiyonu kayıt altına alınır. KVKK uyumu için 7 yıl saklanır.

- Kayıt: kim, ne, ne zaman, hangi IP, hangi kullanıcı agent
- Filtreleme: admin, aksiyon türü, hedef tablo, tarih
- Arama: serbest metin
- Export: CSV, JSON

---

## 5. Yemekçi Paneli

> 📺 **Demo HTML referansı**: `03_Demo_HTML/02_yemekci_panel.html`

Onaylı yemekçi firmaların kullanacağı panel. UYSA dahil hiçbir yemekçi diğerinden ayrıcalıklı değildir; eşit kurallar.

### 5.1 Sol Sidebar Modülleri

- 📊 Dashboard
- 🏢 Firma Profili
- 📄 Belgelerim (ISO, HACCP, vergi levhası)
- 🍽️ Menü Kataloğum
- ⭐ Maliyet ve Fiyatlandırma (6 sekmeli — bkz. Bölüm 8)
- 📞 Talepler (gelen müşteri talepleri)
- 🎯 Proaktif Paketlerim
- 🤝 B2B Pazaryeri (yemekçiler arası)
- 📦 Siparişler
- 📅 Müsaitlik Takvimi
- 👤 Müşterilerim (CRM lite)
- 💬 Mesajlar
- 💰 Finansal (kazanç, komisyon, ödeme geçmişi)
- 📈 Raporlar (kişisel KPI)
- ⚙️ Ayarlar

### 5.2 Dashboard

#### Bekleyen aksiyon kartları:
- Yeni gelen talep sayısı
- Kabul bekleyen sipariş
- Okunmamış mesaj
- Süresi yaklaşan belgeler (30 gün öncesi uyarı)

#### Grafikler ve özet:
- Aylık satış grafiği (kişisel)
- En çok sipariş edilen 5 menüm
- Müşteri puanlarım (ortalama, son 10 yorum)
- Bu ay komisyon kesintisi ve net gelir
- Sıralamadaki konumum (kategori içinde)

### 5.3 Firma Profili

- Temel bilgiler: ad, slug, vergi no, kuruluş yılı, çalışan sayısı
- İletişim: telefon, e-posta, WhatsApp, web sitesi
- Adres: mutfak konumu (harita + işaretleme)
- Hizmet bölgesi: harita üzerinde poligon çiz (Leaflet + OSM)
- Görseller: logo (otomatik kare crop), kapak, mutfak galeri (5-20 fotoğraf)
- Bio (firma hikayesi, max 500 karakter)
- Önizleme: müşterinin gördüğü tam profil görünümü

### 5.4 Menü Kataloğu

- CRUD: standart menülerimi yönet
- Alanlar: kategori, fiyat, kişi aralığı, hazırlık süresi, fotoğraflar
- Menü içindeki yemekleri tek tek tanımla (drag-drop sıralama)
- Diyet etiketleri (helal, vegan, vejetaryen, glutensiz)
- Yayınla / Taslak / Pasif
- Menü kopyala (benzer menü oluşturmak için)

### 5.5 Talepler ve Üç Teklif Türü

> Detaylı: Bölüm 11 - Üç Teklif Türü

- **Tür 1: Reaktif** — Müşteri talebine yanıt
- **Tür 2: Proaktif** — Kendi paketleri (Ramazan, kurumsal aylık vs.)
- **Tür 3: B2B** — Yemekçiler arası tedarik

### 5.6 Sipariş Yönetimi

- Yeni siparişler: kabul / reddet (sebep ile)
- Durum güncelleme: hazırlanıyor → yolda → teslim
- Filtre: durum, tarih, müşteri tipi
- Excel export (muhasebe için)

### 5.7 Müsaitlik Takvimi

- Aylık takvim görünümü
- Belirli günleri 'kapalı' işaretleme
- Günlük max sipariş limiti
- Otomatik kural: günlük X sipariş alınca o gün kapanır

### 5.8 Müşteri Yönetimi (CRM Lite)

- Sipariş veren müşterilerimin listesi
- Müşteri detay: tüm siparişleri, toplam harcama, ortalama tutar
- Cari hesap: kurumsal müşteriler için bakiye takibi
- Müşteri notları (kendi özel notlarım)

### 5.9 Finansal

- Bu ay ne kazandım: brüt satış, komisyon, net alacak
- Ödeme geçmişi: UYSA'dan banka hesabına transferler
- Komisyon faturaları (UYSA'dan kesilen)
- E-fatura kesimi (müşterilere)
- Banka hesap bilgilerini güncelleme

---

## 6. Müşteri Paneli

Hem kurumsal (B2B) hem bireysel (B2C) müşteriler aynı paneli kullanır; bazı modüller user_type'a göre görünür/gizli olur.

### 6.1 Modüller

- 📊 Dashboard (yaklaşan siparişlerim, aktif teklifler, favoriler)
- 📦 Siparişlerim (aktif, geçmiş, iptal)
- 📋 Tekliflerim (gönderdiğim talepler, aldığım teklifler)
- ❤️ Favoriler (menü, yemekçi, paket)
- 📍 Adreslerim
- 🧾 Fatura Bilgilerim
- 💬 Mesajlar
- ⚙️ Profil ve Ayarlar
- 🔔 Bildirim Tercihleri

### 6.2 Sipariş Yönetimi

- Aktif siparişler: durum (kabul beklemede → hazırlanıyor → yolda → teslim)
- Geçmiş siparişler
- İptal edilenler (sebep notları)
- Sipariş detay: tüm bilgiler, fatura görüntüleme, tekrar sipariş
- Sipariş iptal talebi (yemekçi onayına düşer)

### 6.3 Teklif Talep Yönetimi

- Açık taleplerim (kaç teklif geldi, kaç gün kaldı)
- Teklif karşılaştırma sayfası (yan yana)
- Teklif kabul: tek tıkla siparişe dönüşür
- Geçmiş talepler arşivi

### 6.4 Kurumsal Özel (Sadece B2B)

- Şirket bilgileri yönetimi (vergi dairesi, vergi no, e-fatura tercihi)
- Çalışan ekleme (alt kullanıcılar şirket adına sipariş verebilir)
- Onay akışı (X TL üstü siparişler yönetici onayına düşer)
- Cari hesap görüntüleme (yemekçi bazlı)
- Aylık rapor: hangi yemekçiden ne kadar harcadık

---

## 7. Anasayfa Fiyat Hesaplayıcı (9 Soru)

> 📺 **Demo HTML referansı**: `03_Demo_HTML/01_musteri_wizard.html`

Yemekhaneci'nin lansman ürünü ve dönüşüm motoru. Müşteri 30-60 saniyede bölgesindeki ortalama fiyatı öğrenir, mail bırakırsa detaylı liste alır.

### 7.1 Hero Bölümü

- Rozet: "Türkiye'nin tarafsız catering pazaryeri"
- Başlık: "Yemekhanenizin fiyatını 30 saniyede öğrenin."
- Alt metin: "İhtiyaçlarınızı belirtin, bölgenizdeki yemekçilerin ortalama fiyatlarını anında görün."

### 7.2 9 Soru Akışı

#### Soru 1: Kişi Sayısı
- Tek input: sayısal, min 1, max 10.000
- **Akıllı davranış**: Girildiğinde Soru 2'deki Öğle yemeği kişi sayısı otomatik dolar

#### Soru 2: Öğün Dağılımı
- 3 seçenek: Öğle (default seçili), Akşam, Kumanya
- Çoklu seçim — her öğün için ayrı kişi sayısı input'u açılır
- **AKILLI DAĞILIM ALGORİTMASI**:
  - Akşam veya kumanya değişti → öğle otomatik düşer (manuel set edilmemişse)
  - Öğle elle değişti → manuel bayrağı set, kişi sayısı toplamı yeniden hesaplanır
  - Toplam (kişi sayısı) = öğle + akşam + kumanya

#### Soru 3: Menü Yapısı
- **Üst kısım**: CANLI MENÜ ÖNİZLEMESİ (pill'ler ile)
- **Alt kısım**: chip-tabanlı seçim
- Sabit bileşenler: Çorba, Ana yemek, Yardımcı yemek (pilav/makarna), Ekmek
- Salata bar: 1-6 chip seçimi (otomatik dağılım kuralları)
- Tatlı/Meyve/Meşrubat: Dahil / Yok (toggle)
- Ayran/Yoğurt: Sadece Ayran / Sadece Yoğurt / Rotasyon (default) / Birlikte
- Çeşit sayısına göre dinamik segment rozeti: Sade / Ekonomik / Standart / Zengin / Premium

**Salata Bar Otomatik Dağılım Kuralları:**

| Adet | Dağılım |
|------|---------|
| 1 | 1 Salata |
| 2 | 1 Salata + 1 Zeytinyağlı |
| 3 | 2 Salata + 1 Zeytinyağlı |
| 4 | 2 Salata + 1 Zeytinyağlı + 1 Meze |
| 5 | 2 Salata + 1 Zeytinyağlı + 1 Meze + 1 Kısır |
| 6 | 2 Salata + 2 Zeytinyağlı + 1 Meze + 1 Kısır |
| 7+ | Manuel dağılım (yemekçi panelinden tanımlanır) |

#### Soru 4: Hizmet Segmenti
- 3 kart: Ekonomik (📦), Genel (⭐ default), Premium (👑)
- Maliyet matrisinde segment satırını belirler

#### Soru 5: Hizmet Lokasyonu
- Tab 1: Adres yaz (manuel input)
- Tab 2: Konumumu kullan (HTML5 Geolocation API)
- Backend'de yemekçilerin hizmet bölgesi poligonu ile eşleştirilir

#### Soru 6: Personel Desteği
- Toggle: Evet / Hayır (default)
- Evet seçilirse alt input'lar açılır:
  - Aşçı sayısı (0-20)
  - Servis personeli (0-50)
  - Bulaşık/temizlik (0-20)
- Her ek personel maliyeti aylık personel maliyet matrisinden hesaplanır

#### Soru 7: Mutfak Yatırımı
- Toggle: Evet / Hayır (default)
- Evet seçilirse 8 ekipman checkbox listesi:
  - 🔥 Ocak / Set
  - 🧯 Sanayi fırın
  - 🧽 Endüstriyel bulaşık
  - ❄️ Soğutucu dolap
  - 📦 Depo / Kiler
  - 💨 Davlumbaz
  - 🪑 Yemekhane salon
  - 🍲 Self-servis hat
- Her ekipman fiyatı yemekçi panelinden okunur, 12-36 ay amortisman

#### Soru 8: Cumartesi Çalışma
- 3 seçenek: Evet (4 ek gün), Hayır (default), Mesaimiz olduğunda (yarım gün)
- Aylık iş günü sayısını etkiler (22 / 26 / 24)

#### Soru 9: Notlar / Diğer İstekler
- 12 Hızlı seçim etiketi (chip stilinde):
  - 🟢 Helal sertifikalı, 🌱 Vegan, 🌾 Glutensiz
  - 🥜 Fıstık alerjisi, 🐟 Deniz ürünü yok, 🌶️ Acısız
  - 🕌 Ramazan menüsü, 🎓 Öğrenci, 👷 Ağır iş çalışanları
  - 🍽️ Tabaklarımız var, 📦 Tek kullanımlık, 🧊 Soğuk zincir
- Serbest metin alanı (textarea, max 1000 karakter)
- Karakter sayacı (50 kalınca kırmızıya döner)
- Chip toggle: tıklayınca metne ekler/çıkarır

### 7.3 Sonuç Ekranı

#### Üst Kısım: Genel Fiyat Aralığı
- Büyük başlık: Tahmini aylık toplam (alt-üst aralık)
- Alt: Kişi başı/öğün ortalama
- Form özeti (kişi, öğünler, menü yapısı, segment, cumartesi, vb.)
- Hesaplama kaynağı (gerçek yemekçi maliyeti vs. demo)

#### Orta Kısım: ANONİM YEMEKÇİ LİSTESİ
- **Yemekçilerin GERÇEK İSİMLERİ GİZLİ**
- Sadece kod adları: 'Yemekçi A', 'Yemekçi B', vs.
- Her satırda: ikon, kod adı, lokasyon (ilçe + il), puan, tecrübe yılı, sertifikalar
- Sağda: kişi başı/öğün fiyat (büyük rakam)
- Fiyata göre sıralı (ucuzdan pahalıya)
- Hover efekti: hafif sağa kayma + gölge

#### Alt Kısım: MAİL TALEP FORMU
- İki mod (toggle butonları):
  - 📊 **Toplu Liste**: tüm yemekçiler tek mailde, karşılaştırmalı tablo
  - 📬 **Ayrı Mailler**: her firma için ayrı detaylı mail (checkbox listesi açılır)
- E-posta input + KVKK onay checkbox
- Loading state (1.2sn simülasyon)
- Başarı mesajı: yeşil banner ile gösterilir

### 7.4 Mail İçerikleri

#### Toplu Liste Mail Şablonu
- **Konu**: "Yemekhaneci.com.tr — {kisi} kişilik catering teklif listeniz hazır"
- **İçerik**:
  - Talep özeti (tüm form değerleri)
  - Karşılaştırma tablosu: 12 yemekçi yan yana (gerçek isimler!)
  - Her firma için: ad, vergi no, adres, telefon, e-posta, web, fiyat, dahil olanlar
  - Tıklanabilir 'Detay sayfasını aç' linkleri
  - Footer: KVKK metni + iletişim

#### Ayrı Mail Şablonu (Her Firma İçin)
- **Konu**: "{firmaAdı} — Yemekhaneci üzerinden teklif önerinizi inceleyin"
- **İçerik**:
  - Firma profili (logo, hakkımızda, fotoğraflar)
  - Teklif edilen menü detayları
  - Fiyat detayı (kişi başı, aylık toplam)
  - Müşteri yorumları
  - Direkt iletişim için telefon, mail, WhatsApp
  - 'Detaylı görüş' linki (mesajlaşma sayfasına)

#### Yemekçilere Bilgilendirme Maili
Müşteri mail talebinde bulunduğunda, **seçilen yemekçilere de** bildirim gider:
- **Konu**: "Size uygun yeni bir müşteri talebi"
- **İçerik**:
  - Müşteri talebinin özeti
  - Müşteri iletişim bilgileri (mail talebi gönderdiği için izin var)
  - Direkt iletişim/teklif gönder linki

---

## 8. Maliyet Tabanlı Fiyatlandırma Motoru

Yemekhaneci'nin teknik kalbi. Yemekçi panelinde 6 sekmede tanımlanan maliyetler, müşteri wizard'ında dinamik fiyat hesaplaması üretir.

### 8.1 Yemekçi Panelinde 6 Sekme

#### Sekme 1: Yemek Maliyeti Matrisi
- 3×4 matris: Segment (Ekonomik/Genel/Premium) × Çeşit (3/4/5/6+)
- Her hücre: TL/öğün (sadece hammadde + üretim, personel hariç)
- Renkli kategoriler: Ekonomik (yeşil), Genel (mavi), Premium (bordo)
- Veri tipi: DECIMAL(10,2)

#### Sekme 2: Personel (Pozisyon + Deneyim)
- 3 grup, 9 pozisyon:
  - **Mutfak**: Aşçıbaşı (Senior), Aşçı (Senior), Aşçı (Junior), Yardımcı
  - **Servis**: Servis Şefi (Senior), Servis Personeli (Junior)
  - **Destek**: Bulaşıkçı, Şoför, Operasyon Sorumlusu
- Her pozisyon: aylık brüt maliyet (TL/ay)
- **Şablon Atama**: 100 kişilik sipariş için varsayılan personel sayıları
- Hibrit yaklaşım: şablon var, sipariş başına değiştirilebilir

#### Sekme 3: Sabit Giderler
- 7 kalem aylık gider: Mutfak Kira, Elektrik/Su/Gaz, Araç, İşletme Sigortası, Muhasebe, Dijital, Diğer Overhead
- **Manuel Dağıtım Oranı slider** (%5-50): Bu siparişe yüklenen sabit gider yüzdesi

#### Sekme 4: Mutfak Yatırım
- 8 ekipman birim fiyatı (yemekçinin gerçek alış fiyatı)
- **Amortisman süresi slider** (12-60 ay)
- Müşteri seçtiği ekipmana göre aylık dağıtım eklenir

#### Sekme 5: Kar Marjı (3 Bileşen Kombine)
- **A. Segment marjı**: Ekonomik (%18 default), Genel (%25), Premium (%35)
- **B. Hacim indirimi**: 100+ (-2%), 200+ (-4%), 500+ (-7%)
- **C. Genel düzeltme**: -%15 ila +%20 (yemekçinin manuel ayarı)
- `nihai_marj = A + B + C`

#### Sekme 6: Menü Şablonu
- Sabit bileşenler tanımı (Çorba, Ana, Yardımcı, Ekmek)
- Salata bar dağılım kuralları (1-4 otomatik, 5+ manuel)
- Tatlı/Meyve/Meşrubat döngü seçenekleri
- Ayran/Yoğurt yaklaşımı (Sabit Ayran, Sabit Yoğurt, İkisi, Menüye Göre)
- Canlı menü pill önizlemesi + dinamik badge
- **MALİYET KAÇAĞI YOK**: her bileşen toplam çeşit sayısına dahildir

### 8.2 Tam Hesaplama Formülü

```
═══════════════════════════════════════════════════════════════
NİHAİ ÖĞÜN FİYATI HESAPLAMA FORMÜLÜ
═══════════════════════════════════════════════════════════════

ADIM 1 — HAMMADE/YEMEK MALİYETİ
   yemek_maliyeti = matris[segment][çeşit_sayısı]
   Örn: matris['genel']['6'] = 110 TL/öğün

ADIM 2 — PERSONEL MALİYETİ DAĞITIMI
   sipariş_personel_maliyeti = Σ(pozisyon_aylık_maliyeti × adet)
   personel_per_ogun = sipariş_personel_maliyeti / aylık_toplam_ogun
   Burada aylık_toplam_ogun = günlük_kişi × ay_içi_iş_günü

ADIM 3 — SABİT GİDER DAĞITIMI (manuel oran)
   aylık_sabit_gider = sum(7 kalem)
   sabit_gider_payi = aylık_sabit_gider × (manuel_oran / 100)
   sabit_per_ogun = sabit_gider_payi / aylık_toplam_ogun

ADIM 4 — MUTFAK YATIRIM (varsa)
   mutfak_aylık = mutfak_yatırım_toplamı / amortisman_ay
   mutfak_per_ogun = mutfak_aylık / aylık_toplam_ogun

ADIM 5 — BİRİM MALİYET TOPLAMI
   birim_maliyet = yemek_maliyeti
                 + personel_per_ogun
                 + sabit_per_ogun
                 + mutfak_per_ogun

ADIM 6 — KAR MARJI (TÜMÜ KOMBİNE)
   marj_segment = matris_marj[segment]
   marj_hacim = if (kişi >= 500) -7%
                elif (kişi >= 200) -4%
                elif (kişi >= 100) -2%
                else 0%
   marj_ozel = yemekçi_manuel_düzeltme
   nihai_marj = marj_segment + marj_hacim + marj_ozel

ADIM 7 — SATIŞ FİYATI (KİŞİ BAŞI/ÖĞÜN)
   satış_fiyati_per_ogun = birim_maliyet × (1 + nihai_marj/100)

ADIM 8 — AYLIK TOPLAM
   aylık_satış = satış_fiyati_per_ogun × aylık_toplam_ogun

ADIM 9 — CUMARTESİ ETKİSİ
   ay_içi_iş_günü = if (cumartesi_evet) 26
                    elif (cumartesi_yarim) 24
                    else 22

ADIM 10 — ARALIKLANDIRMA (UI için)
   alt_sınır = aylık_satış × 0.85
   üst_sınır = aylık_satış × 1.15
═══════════════════════════════════════════════════════════════
```

### 8.3 Müşteri Tarafında Eşleştirme Mantığı

Müşteri wizard'ı doldurduğunda, backend şu adımları çalıştırır:

1. **Lokasyondan poligon kontrolü** → o bölgede hizmet veren yemekçileri filtrele
2. **Onaylı + aktif + yeterli kapasiteli** olanları al
3. Her yemekçi için yukarıdaki formülü çalıştır (kendi maliyet matrisi)
4. **Anonim listeyi fiyata göre sırala** (ucuzdan pahalıya)
5. Kullanıcıya min-max aralık göster + anonim liste

---

## 9. Menü Şablonu Yapısı

UYSA'nın Mayıs 2026 menüsü baz alınarak tasarlanmıştır. Endüstriyel toplu yemek standartlarını yansıtır.

### 9.1 Sabit Yapı (Her Menüde Var)

- 🥣 **Çorba** — Mercimek, Ezogelin, Şehriye, Tarhana, Düğün, Yayla, Tavuksuyu rotasyonu
- 🍖 **Ana Yemek** — Et/tavuk/sebze (döner, kebap, köfte, kavurma, dolma)
- 🍚 **Yardımcı Yemek** — Pilav/makarna/börek/erişte
- 🍞 **Ekmek** — 2 dilim, 272 kkal

### 9.2 Dinamik: Salata Bar

1-6 çeşit otomatik dağılım kuralları için bkz. Bölüm 7.2 (Soru 3)

7+ çeşit için yemekçi panelinde manuel dağılım: Salata, Zeytinyağlı, Meze, Kısır kategorilerinde adet belirleme.

### 9.3 Dinamik: Tatlı/Meyve/Meşrubat

- Var/Yok seçimi (toggle)
- Varsa 3 alt seçenek (checkbox): Tatlı, Meyve, Meşrubat
- Tatlı çeşitleri: Sütlaç, Supangle, Magnolya, Kazandibi, Trileçe, Brownie, Şekerpare, Kek
- Haftalık rotasyon: yemekçi günlük menüde hangisini servis edeceğine karar verir

### 9.4 Dinamik: Ayran/Yoğurt

- 4 yaklaşım:
  - Sabit Ayran: her öğün ayran, 1 çeşit
  - Sabit Yoğurt: her öğün yoğurt, 1 çeşit
  - Sabit İkisi: hem ayran hem yoğurt, 2 çeşit (toplam çeşit artar)
  - Menüye Göre Rotasyon (default): akıllı eşleştirme, 1 çeşit
- Akıllı eşleştirme örnekleri:
  - Etli ana → Ayran
  - Sebze/dolma → Yoğurt
  - Hamur işi → Ayran
  - Acılı/baharatlı → Yoğurt

### 9.5 Maliyet Kaçağı Olmaması Garantisi

> ⚠️ **KRİTİK**: Menüde olan HER bileşen (sabit ayran/yoğurt dahil) toplam çeşit sayısına eklenir. Sistemde 'Hayır' seçilen tek şey 'Tatlı/Meyve/Meşrubat = Yok' durumudur — bu 1 çeşit azaltır.

### 9.6 Çeşit Sayısı → Segment Rozeti

| Çeşit | Rozet | Açıklama |
|-------|-------|----------|
| ≤4 | Sade | Tatlı yok, salata yok, minimal yapı |
| 5 | Ekonomik | 1 salata + tatlı yok veya tatlı + salata yok |
| 6 | Standart | UYSA Mayıs menüsü gibi standart yapı |
| 7 | Zengin | Ekstra salata veya ikisi-de ayran |
| 8+ | Premium | Davet/etkinlik tarzı zengin menü |

---

## 10. Anonim Liste ve Mail Sistemi

Yemekhaneci'nin diferansiyel UX'i. Müşteri yemekçi isimlerini görmeden fiyat karşılaştırması yapar; sadece e-posta ile detaylı listeyi alır.

### 10.1 Stratejik Faydalar

- 🔒 **Anonimlik = Yemekçi güveni**: 'Platforma kaydolayım, müşteri direkt arar' korkusu yok
- 📧 **Mail = Lead capture**: E-posta bırakmadan firma görmek imkansız → her hesaplama bir lead
- ⚖️ **Şeffaf rekabet**: Müşteri 12 fiyatı yan yana görür, tek başına değerlendirir
- 🎯 **Kontrollü iletişim**: Müşteri 'tüm yemekçilerle değil, ilk 5 ile' diye seçebilir
- 📊 **Veri zenginliği**: Backend'e müşteri talebi + mail + tercih akışı

### 10.2 Anonim Liste Üretimi

Backend'de şu adımlar çalışır:

1. Hizmet bölgesi poligonu × müşteri lokasyonu eşleştirme
2. Onaylı + aktif yemekçileri filtrele (`status='approved'`)
3. Müsait kapasiteli olanları al (kontrat yoksa veya yeni alabilir)
4. Her yemekçi için Bölüm 8'deki formül ile fiyat hesapla
5. Anonim kod ata: 'Yemekçi A', 'Yemekçi B', ... (rastgele harfler)
6. Fiyata göre sırala (ucuzdan pahalıya)
7. Frontend'e JSON gönder: id, anonim_kod, bolge, fiyat, puan, tecrube, belgeler

### 10.3 Mail Modu Seçimi

#### Toplu Liste
- Tüm yemekçiler tek mailde
- Karşılaştırmalı tablo (HTML mail)
- Hızlı genel bakış istemek isteyenler için

#### Ayrı Mailler
- Her firma için ayrı detaylı mail
- Müşteri checkbox listesinden seçer (Tümünü seç butonu da var)
- Her mail: firma profili, foto galeri, fiyat detayı, müşteri yorumları, direkt iletişim

### 10.4 Mail Gönderim Akışı

1. Müşteri formu doldurur, mail adresi girer, KVKK onayı verir
2. Backend: queue'ya e-posta gönderim job'u ekler
3. Worker: PostMark/Sendgrid ile mail gönderir
4. Müşteriye mail iletilir (gerçek isimler, iletişim bilgileri)
5. **AYRICA**: seçilen yemekçilere de bildirim mail (yeni potansiyel müşteri)
6. `leads` tablosuna kayıt: müşteri mail, talep özeti, seçilen yemekçi ID'leri
7. Admin paneline gerçek zamanlı 'Yeni Lead' bildirimi

### 10.5 KVKK Uyumu

- Mail formunda KVKK aydınlatma onayı zorunlu
- Onay kayıt altında tutulur (tarih, IP, user agent)
- Müşteri tek mail ile yemekçilere izin vermiş sayılır
- Müşteri istediği zaman 'verilerimi sil' talebinde bulunabilir

---

## 11. Üç Teklif Türü

Platform üç farklı teklif türünü tek yapıda barındırır. Her birinin kendi user flow'u, DB tablosu ve komisyon oranı vardır.

| Özellik | Tür 1: Reaktif | Tür 2: Proaktif | Tür 3: B2B |
|---------|----------------|------------------|------------|
| Kim başlatır | Müşteri (talep) | Yemekçi (paket) | Yemekçi (ihtiyaç/teklif) |
| Hedef alıcı | B2B + B2C | B2B + B2C | Sadece yemekçiler |
| Etkileşim | Müzakereli (mesajlaşma) | Direkt sepete ekle | Müzakereli |
| Komisyon | %5-7 | %8-12 | %3-5 |
| Onay süreci | Yok | Admin onayı | Yok |
| Süre | 24 saat | Yemekçi belirler | Yemekçi belirler |

### 11.1 Tür 1: Reaktif Teklif

Müşteri özel ihtiyaç için talep oluşturur, sistem uygun yemekçilere bildirir, yemekçiler teklif verir, müşteri seçer.

**Akış:**
1. Müşteri `/teklif-al` sayfasına gider
2. Form: tarih, kişi sayısı, bütçe, lokasyon, özel istekler
3. Sistem eşleştirir: hizmet bölgesi + müsait + uygun bütçe
4. Maks 10 yemekçiye bildirim (e-posta + SMS + panel)
5. Yemekçiler 24 saat içinde teklif verir
6. Müşteri panelden tüm teklifleri yan yana karşılaştırır
7. Birini seçer → ödeme → sipariş

### 11.2 Tür 2: Proaktif Paket

Yemekçi kendi inisiyatifiyle özel paketler oluşturur, admin onayından sonra marketplace'te ekstra alanda görünür.

**Örnek paketler:**
- Ramazan 30 günlük iftar paketi
- Bayram özel kahvaltı (3 günlük)
- Kurumsal aylık öğle paketi (50 kişi × 22 iş günü)
- Yeni yıl menüsü (sınırlı 100 sipariş)
- Düğün VIP catering paketi

**Akış:**
1. Yemekçi `/yemekci/paketler/yeni`
2. Bilgileri girer: ad, açıklama, içerik, fiyat, eski fiyat, başlangıç/bitiş, max sipariş
3. Onaya gönderir → admin 24 saat içinde inceler
4. Onay sonrası `/paketler` sayfasında yayınlanır
5. Anasayfada 'Bu Haftanın Özel Paketleri' bölümünde de görünür
6. Müşteri direkt sepete ekler → ödeme yapar
7. Max sipariş dolduğunda otomatik kapanır

### 11.3 Tür 3: B2B Tedarik

Yemekçiler arası kapalı pazaryeri. B2C müşteriler bu bölümü görmez.

**İki yön:**
- İhtiyaç (bir yemekçi başka yemekçiden hizmet/ürün ister)
- Teklif (bir yemekçi başka yemekçilere hizmet/ürün sunar)

**Kategoriler:**
- Kapasite kiralama (büyük etkinlikte ek mutfak)
- Tatlı tedarik (uzman tatlıcıdan)
- Hammadde toptan satış (et, sebze, baharat)
- Personel kiralama (geçici)
- Ekipman kiralama

---

## 12. Gelir Modeli

| Kanal | Hedef | Oran/Ücret | Aktive Zamanı |
|-------|-------|------------|----------------|
| Direkt Sipariş Komisyonu | Standart menüden satış | %8-12 | MVP Faz 1 |
| Reaktif Teklif Komisyonu | Müşteri talebine yanıt | %5-7 | MVP Faz 1 |
| Proaktif Paket Komisyonu | Yemekçi paketleri | %8-12 | MVP Faz 2 |
| B2B Tedarik Komisyonu | Yemekçi-yemekçi | %3-5 | Faz 3 (6. ay) |
| Premium Üyelik | Yemekçi ek özellikleri | 499₺/999₺ ay | Faz 3 (6. ay) |
| Operasyon SaaS | Menü plan, yoklama, fatura | 199-1499₺/ay | Faz 4 (9. ay) |
| Reklam / Öne Çıkarma | Listelemede üst sıra | CPC veya sabit | Faz 2 (6. ay) |

### 12.1 Strateji Notu

İlk 3 ay sadece komisyon modeli ile başlanmalı. Yemekçilere 'sıfır risk' söylemi (sadece sipariş gelirse para alınır) ile platforma çekilmeleri kolaylaşır. Premium üyelik ve SaaS modülleri kritik kütle (~100 yemekçi) oluştuktan sonra eklenir.

---

## 13. Veritabanı Şeması (24 Tablo)

> 📁 **SQL referansı**: `08_Veritabani/database_schema.sql`

MySQL 8 + utf8mb4_turkish_ci. Her tabloda otomatik id (BIGINT UNSIGNED), created_at, updated_at. Soft delete kullanan tablolarda deleted_at.

### Tablolar Listesi

1. **users** — Tüm kullanıcılar (kurumsal, bireysel, yemekçi, admin) - tek tabloda
2. **corporate_profiles** — Kurumsal müşteri detayları
3. **supplier_profiles** — Yemekçi profilleri
4. **supplier_documents** — Yemekçi yasal belgeleri
5. **supplier_service_areas** — Yemekçi hizmet bölgeleri (poligon)
6. **supplier_cost_matrix** — Yemekçi yemek maliyet matrisi (3 segment × 4 çeşit)
7. **supplier_personnel_costs** — Yemekçi personel maliyetleri (pozisyon × deneyim)
8. **supplier_personnel_template** — 100 kişilik sipariş için varsayılan personel atama
9. **supplier_fixed_costs** — Yemekçi sabit aylık giderler
10. **supplier_kitchen_equipment** — Mutfak ekipman birim fiyatları
11. **supplier_profit_margin** — Kar marjı stratejisi
12. **supplier_menu_template** — Menü şablonu yapısı
13. **categories** — Menü kategorileri (parent/child)
14. **menus** — Yemekçi standart menüleri
15. **menu_items** — Bir menüdeki yemekler
16. **menu_images** — Menü ek fotoğrafları
17. **proactive_packages** — Yemekçi proaktif paketleri
18. **leads** — Wizard'dan gelen müşteri talepleri (mail isteği)
19. **quote_requests** — Özel teklif talepleri (Tür 1)
20. **quotes** — Yemekçilerin tekliflere yanıtları
21. **b2b_listings** — B2B pazaryeri ilanları (Tür 3)
22. **orders** — Tüm siparişler
23. **messages** — Platform içi mesajlaşma
24. **reviews / payments / notifications / favorites / activity_log** — Yardımcı tablolar

Detaylı sütun listesi için `08_Veritabani/database_schema.sql` dosyasına bakın.

---

## 14. Teknik Mimari

### 14.1 Stack

| Katman | Teknoloji | Gerekçe |
|--------|-----------|---------|
| Backend | PHP 8.2 + Laravel 11 (veya saf PHP) | Mevcut deneyim |
| Veritabanı | MySQL 8 + Redis | Stabilite |
| Frontend Web | HTML/CSS/JS + Bootstrap 5 + Alpine.js | Hızlı, SEO-friendly |
| Frontend Admin | React 19 | Karmaşık dashboard |
| Görsel İşleme | Imagick + WebP + Cloudflare CDN | Performans |
| Ödeme | iyzico, PayTR, Banka havalesi | TR standart |
| E-Fatura | ParamPos veya Foriba | Yasal |
| E-Posta | PostMark veya SendGrid | Deliverability |
| SMS | Netgsm veya İletimerkezi | Türkçe |
| Sunucu | Hostinger VPS Ubuntu 22.04 + Traefik + SSL | Mevcut |
| Arama | MySQL FULLTEXT → Meilisearch | Maliyet |
| Harita | Leaflet + OpenStreetMap | Ücretsiz |
| Yedek | Günlük MySQL → Backblaze B2 | DR |
| Monitor | UptimeRobot + Sentry | İzleme |

### 14.2 Klasör Yapısı

```
yemekhaneci/
├── public/                    # Web kök
│   ├── index.php
│   ├── assets/                # css, js, img (build edilmiş)
│   └── uploads/               # kullanıcı yüklemeleri
├── app/
│   ├── Controllers/
│   │   ├── Admin/
│   │   ├── Supplier/
│   │   ├── Customer/
│   │   └── Public/
│   ├── Models/
│   ├── Services/              # iş mantığı
│   │   ├── PriceCalculator.php
│   │   ├── SupplierMatcher.php
│   │   ├── MailService.php
│   │   └── PaymentService.php
│   ├── Repositories/
│   ├── Middleware/
│   ├── Validators/
│   ├── Helpers/
│   └── Jobs/                  # queue jobları
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── public/
│   │   ├── admin/
│   │   ├── supplier/
│   │   └── customer/
│   └── lang/tr/
├── routes/
│   ├── web.php
│   ├── api.php
│   ├── admin.php
│   └── supplier.php
├── storage/
├── tests/
├── .env.example
└── composer.json
```

### 14.3 Güvenlik (Tavizsiz)

- HTTPS zorunlu (Traefik + Let's Encrypt)
- CSRF token her form'da
- Rate limiting Redis tabanlı (login: 5/15dk, talep: 10/saat)
- SQL injection: prepared statements zorunlu
- XSS: htmlspecialchars() her çıktıda
- Şifre: Argon2id
- 2FA: admin için zorunlu, kullanıcı için opsiyonel
- Session: httponly, secure, samesite=lax
- File upload: MIME type whitelist, max boyut, random isim
- KVKK: çerez izni, veri silme talebi, audit log 7 yıl
- Ödeme callback: gateway imzası doğrulanmadan işlem yok
- Idempotent: aynı transaction 2 kere işlenmesin

---

## 15. Sayfa Haritası ve User Flow

### 15.1 Public Sayfalar
- `/` — Anasayfa (hero + 9 soru wizard)
- `/menuler` — Tüm menü listeleme + filtreleme
- `/menu/{slug}` — Menü detay sayfası
- `/firma/{slug}` — Yemekçi profil sayfası
- `/paketler` — Proaktif paketler listeleme
- `/paket/{slug}` — Paket detay
- `/teklif-al` — Özel teklif formu (giriş zorunlu)
- `/nasil-calisir` — Müşteri rehberi
- `/yemekci-ol` — Yemekçi tanıtım sayfası
- `/hakkimizda`, `/iletisim`, `/sss`
- `/kvkk`, `/sozlesme`, `/cerez-politikasi`
- `/giris`, `/kayit`, `/sifre-sifirla`

### 15.2 Müşteri Paneli (`/hesabim`)
- `/dashboard`, `/siparislerim`, `/tekliflerim`, `/favoriler`, `/adreslerim`, `/fatura-bilgilerim`, `/mesajlar`, `/profil`, `/bildirim-tercihleri`

### 15.3 Yemekçi Paneli (`/yemekci`)
- `/dashboard`, `/profil`, `/belgeler`, `/menuler`, `/maliyet`, `/talepler`, `/paketler`, `/b2b`, `/siparisler`, `/takvim`, `/musteriler`, `/mesajlar`, `/finansal`, `/raporlar`, `/ayarlar`

### 15.4 Admin Paneli (`/yonetim`)
Bkz. Bölüm 4.1

### 15.5 Kritik User Flow Örnekleri

#### Flow A: Wizard → Mail → Yemekçi Eşleşmesi (En kritik)
1. Anasayfa → wizard 9 soru → Hesapla → Anonim liste + ortalama fiyat
2. Mail formu doldur → KVKK onay → Gönder → Loading 1.2sn
3. Backend: lead kaydet, queue'ya mail jobu ekle
4. Worker: müşteriye toplu liste maili (gerçek isimlerle)
5. Worker: seçilen yemekçilere de bildirim maili
6. Müşteri yemekçilerle direkt iletişime geçer (mail/telefon)

#### Flow B: Kurumsal müşteri → Direkt sipariş
- Anasayfa → `/menuler` → filtre → menü detay → sepete ekle → tarih/saat/adres → ödeme (havale veya kart) → onay → e-posta+SMS

#### Flow C: Yemekçi onaylanma süreci
- `/yemekci-ol` → form (firma + belgeler) → status=pending → admin onayı (24-48 saat) → onay sonrası panele tam erişim → profil tamamla → menü ve maliyet doldur → yayınla

---

## 16. UX/UI Tasarım Sistemi

### 16.1 Marka Renkleri

```css
--brand-primary:      #6B1F2A   /* derin bordo - ana renk */
--brand-primary-soft: #8B2635   /* hover, soft varyant */
--brand-accent:       #C9A961   /* eski altın - vurgu */
--brand-cream:        #FAF6F0   /* krem zemin */
--brand-cream-dark:   #F0E9DD   /* krem koyu */
--ink:                #1F1815   /* ana metin */
--ink-soft:           #5A4A42   /* alt metin */
--ink-muted:          #8B7E76   /* placeholder, muted */
--line:               #E5DCD0   /* kenarlıklar */

--success:            #2F6B4A
--warning:            #C9882B
--danger:             #C53030
--info:               #1B5E7C
--purple:             #6B46C1
```

### 16.2 Tipografi

- **Başlıklar**: Cormorant Garamond (serif, kurumsal asil)
- **Gövde**: Manrope (sans-serif, modern)
- **Monospace (sayısal)**: JetBrains Mono
- rem tabanlı boyutlandırma (px değil)

### 16.3 Bileşen Kütüphanesi

- **Buttons**: btn-primary, btn-ghost, btn-success, btn-danger
- **Inputs**: text, number, email, textarea, select, slider, toggle
- **Cards**: kpi-card, info-box, warning-box, success-box
- **Tables**: data-table (responsive), with row-actions
- **Badges**: status badges (5 renk varyantı)
- **Chips**: selectable, fixed, tag
- **Modals**: confirmation, detail, form
- **Toasts**: success, error, info, warning
- **Loading**: spinner, shimmer, progress bar

### 16.4 Responsive Breakpoints

- Mobile: < 640px (mobile-first design)
- Tablet: 641-1024px
- Desktop: > 1024px
- Tüm dokunma hedefleri min 44×44px (Apple/Google standardı)

---

## 17. Sezgisellik ve Kolay Kullanım

> Bu bölüm Faz 1 ve Faz 2'de uygulanacak UX iyileştirmelerini içerir.

### 17.1 Step Progress Indicator (MVP - Kritik)

9 soruluk wizard'da kullanıcının nerede olduğunu net göstermek.
- Üstte sticky progress bar
- Format: "Adım 4/9 — Lokasyon"
- Doluluk yüzdesi animasyonlu
- Tıklayarak geçmiş adımlara dönülebilir
- **Conversion etkisi: +%15-25**

### 17.2 Form Save / Otomatik Taslak Kayıt (MVP - Kritik)

- localStorage tabanlı otomatik save (her input change'inde)
- Tarayıcı kapatılıp tekrar açıldığında: "Önceki hesaplamanıza devam etmek ister misiniz?"
- Devam veya temizle seçimi
- 24 saat sonra otomatik temizleme

### 17.3 Hesaplama Şeffaflığı (MVP - Kritik)

Sonuç ekranında "🔍 Hesaplama detayı" linki (collapsible). Açılınca:

```
Hammadde:           75 ₺
Personel payı:      28 ₺  (saha personeli + mutfak)
Sabit gider payı:    5 ₺  (kira, elektrik amortismanı)
Mutfak yatırımı:     8 ₺  (12 ay amortisman)
─────────────────────────
Birim maliyet:     116 ₺
Cumartesi etkisi:   +%18 (4 ek gün)
Hacim indirimi:     -%4  (200+ kişi)
Kar marjı:         +%25
─────────────────────────
Satış fiyatı:      182 ₺/öğün
```

### 17.4 Loading States ve Animasyonlar (MVP)
- "Hesapla" tıklanınca 800ms-1.2sn delay + shimmer effect
- Yemekçi listesi yüklenirken gri pulsing kartlar
- Sayılar değişirken count-up animasyonu
- Pill'ler eklenirken bounce/scale-in animasyonu

### 17.5 Mobil Optimizasyon (MVP - Kritik)
- Tüm dokunma hedefleri min 44×44px
- Sticky elementler (header, progress bar, hesapla butonu)
- Bottom-sheet modallar (mobile için)

### 17.6 Onboarding Tour (Post-MVP)
Yemekçi paneline ilk girişte 5-7 adımlı tur (Intro.js veya Shepherd.js)

### 17.7 Akıllı Karşılaştırma (Post-MVP)
Yemekçi maliyet doldururken her input yanında "i" tooltip → "Bölgenizdeki ortalama: 68-82 TL"

### 17.8 Otomatik Kalite Skoru (Post-MVP)
"Profil Tamamlanma: %68" göstergesi + checklist + "kaliteli yemekçi" rozeti

### 17.9 Akıllı Varsayılanlar (Post-MVP)
Tüm hücreler 0 yerine Türkiye 2026 ortalaması placeholder (gri renkte, yazınca silinir)

### 17.10 Çıktı Formatları (Post-MVP)
- Müşteri sonuç ekranında "📄 PDF olarak kaydet" + "📊 Excel'e aktar" butonları

### 17.11 Karşılaştırma Modu (Post-MVP)
Anonim listede "3 yemekçiyi yan yana karşılaştır" özelliği

### 17.12 WhatsApp Bildirimi (Post-MVP)
WhatsApp Business API: "12 teklif geldi, görüntülemek için tıklayın"

### 17.13 Ses Tabanlı Giriş (Faz 4)
Whisper API: "100 kişilik fabrika için öğle yemeği teklifi al" → form otomatik doldurulur

---

## 18. Kurumsallık ve Güven

### 18.1 Sosyal Kanıt (MVP)
- "500+ Aktif Yemekçi" / "50.000+ Aylık Öğün" / "₺120M+ Aylık GMV"
- KVKK uyum logosu, TÜRKAK akreditasyon logosu
- Güvenli ödeme rozetleri (iyzico, MasterCard, Visa)
- SSL sertifika rozet (footer)

### 18.2 Müşteri Logoları (MVP)
- "Bu firmalar Yemekhaneci ile çalışıyor" bölümü
- UYSA gerçek müşterileri (izinler alınmış)
- Tek satırda 8-12 logo (grayscale, hover'da renkli)

### 18.3 Yasal Sayfalar (MVP - Zorunlu)
- KVKK Aydınlatma Metni (mecburi)
- Mesafeli Satış Sözleşmesi
- Çerez Politikası (cookie banner zorunlu)
- Kullanım Koşulları
- Yemekçi Sözleşmesi (B2B)
- Müşteri Hizmetleri SSS
- Hepsi footer'da link

### 18.4 İletişim Şeffaflığı (MVP)
- Footer'da fiziksel adres (UYSA gerçek adresi)
- Vergi numarası footer'da
- Telefon (gerçek, cevaplanan)
- WhatsApp Business API
- Çalışma saatleri belirgin

### 18.5 Profesyonel İçerik (MVP)
- "Hakkımızda" sayfası: UYSA'nın 20+ yıllık geçmişi, MÜSİAD üyeliği
- Ekip fotoğrafları (gerçek, profesyonel çekim)
- Misyon, vizyon, değerler
- Basında biz / referanslar bölümü

### 18.6 Marka Tutarlılığı
- Tüm panellerde aynı logo, renk, font
- E-posta şablonları kurumsal görünüm
- Fatura ve sözleşme şablonları profesyonel
- Sosyal medya hesapları (LinkedIn, Instagram aktif)

---

## 19. Faz Yol Haritası

| Faz | Süre | Çıktılar | Kilometre Taşı |
|-----|------|----------|-----------------|
| **Faz 0: Setup** | 1 hafta | VPS hazırlık, domain DNS, Traefik+SSL, MySQL, repo, dev ortamı | Boş site canlı |
| **Faz 1: Temel** | 3 hafta | DB migration (24 tablo), kullanıcı kayıt/giriş (3 tip), yemekçi profil, admin onay, KVKK formları | İlk yemekçi onayı |
| **Faz 2: Maliyet** | 3 hafta | Yemekçi panel 6 sekme + canlı önizleme | İlk yemekçi maliyetlerini girer |
| **Faz 3: Wizard** | 3 hafta | 9 soru wizard, fiyat motoru, anonim liste, Step Progress + Form Save + Loading | İlk fiyat hesaplaması |
| **Faz 4: Mail** | 2 hafta | Lead capture, mail gönderim, toplu/ayrı mod, KVKK uyumu | İlk gerçek lead |
| **Faz 5: Menü** | 2 hafta | Yemekçi menü kataloğu, müşteri tarama + filtre + arama | Menü pazaryeri açık |
| **Faz 6: Sipariş** | 3 hafta | Sepet, ödeme (iyzico+havale), sipariş yönetim, e-posta+SMS | İlk gerçek sipariş |
| **Faz 7: Teklif** | 2 hafta | Reaktif teklif, mesajlaşma, yorum sistemi | MVP lansman |
| **Faz 8: Beta** | 3 hafta | 5-10 pilot yemekçi ile kapalı beta, hata düzeltme | Açık lansman hazır |
| **Faz 9: Lansman** | Sürekli | Pazarlama, MÜSİAD + LinkedIn + Instagram, 100 yemekçi, SEO | PR + büyüme |

**Toplam tahmini süre**: MVP lansmanına 19-22 hafta (~5 ay), açık lansmana 24-26 hafta (~6 ay).

---

## 20. Roadmap ve Backlog

### 20.1 Faz 2 (Lansmandan 1-3 Ay Sonra)
- Onboarding Tour (yemekçi paneli)
- PDF/Excel çıktı butonları
- Akıllı placeholder değerler
- Profil tamamlanma skoru ve rozet
- WhatsApp Business API bildirim
- Sosyal kanıt zenginleştirme
- Müşteri logoları bölümü
- Karşılaştırma modu (3 yemekçi yan yana)
- Tooltip ile rakip ortalama bilgileri

### 20.2 Faz 3 (3-6 Ay)
- Proaktif Paket sistemi (Tür 2)
- B2B Tedarik pazaryeri (Tür 3)
- Premium üyelik
- Reklam ve öne çıkarma
- Kupon ve kampanya yönetimi
- Yemekçi kapasite takvimi gelişmiş

### 20.3 Faz 4 (6-12 Ay)
- Operasyon SaaS modülleri
- Mobil uygulama (iOS + Android)
- Ses tabanlı giriş (Whisper API)
- Çoklu dil (en, ar)
- Public API
- AI tabanlı içerik moderasyonu
- Tahmin modelleri

---

## 21. Risk Analizi

| Risk | Etki | Azaltma |
|------|------|---------|
| yemekhanem.com.tr ile rekabet | Müşteri kazanımı zorlaşır | Maliyet matrisi + anonim liste + operasyon SaaS ile farklılaş; UYSA referans gücü |
| Yemekçi yok (likidite) | Ölü pazaryeri | İlk 6 ay sıfır komisyon; UYSA ağındaki yemekçiler seed |
| Yemekçi platform dışına kayar | Komisyon kaybı | Ödeme akışı içeride; iletişim bilgisi paylaşımı bloke |
| Düşük kaliteli yemekçi | Marka itibarı zarar | Sıkı KYC + 3 negatif şikayet → otomatik askıya alma |
| Ödeme dispute | Mali kayıp | Escrow benzeri tutma (teslim sonrası 24 saat) |
| KVKK / e-fatura uyumsuzluğu | Yasal yaptırım | Avukat + mali müşavir review; e-fatura MVP'de |
| Kapasite aşımı | Sipariş iptali | Müsaitlik takvimi + günlük max sipariş limiti |
| Yanlış maliyet matrisi | Yanlış fiyat | Setup wizard zorunlu + admin onay + tooltip referans |
| Spam / fake hesaplar | Sistem güvenliği | Rate limit, Captcha, e-posta + telefon doğrulama |

---

## 22. Claude Code Disiplini

### 22.1 Faz Bazlı Komut Şablonu

```
[Faz N - kısa başlık]

Bağlam: PRD.md bölüm X.Y'yi oku. CLAUDE.md kurallarını uygula.

Yapılacak:
- net liste 1
- net liste 2

Çıktı:
- üretilen dosyalar
- test komutları
- README'ye eklenecek bölüm

Kurallar:
- (varsa özel kurallar)
```

### 22.2 Yasak Davranışlar

- ❌ "Bonus olarak şunu da ekledim" — sadece istenen yapılır
- ❌ "Daha iyi olur diye değiştirdim" — değiştirmek için önce sor
- ❌ Composer/npm paket ekleme (sormadan)
- ❌ Migration ile veri silme/değiştirme (sormadan)
- ❌ .env dosyasını commit'leme
- ❌ Direkt SQL — sadece migration üzerinden

### 22.3 Beklenen Davranışlar

- ✅ Belirsizse "Bunu şöyle anlıyorum, doğru mu?" diye sorar
- ✅ Hata varsa root cause'u bulur, üstünü örtmez
- ✅ Her dosyada en üste 1-2 satır docblock
- ✅ TODO'ları açık yazar: `// TODO: Faz 4'te queue'ya taşınacak`
- ✅ Her faz commit'lenir, staging'e deploy

### 22.4 İlerleme Takibi

- Her oturum sonunda PROGRESS.md güncellenir
- Önemli teknik kararlar DECISIONS.md'ye yazılır
- Bug'lar ISSUES.md'de takip edilir
- Faz sonu retrospektif: ne iyi gitti, ne zorladı

---

## 23. Lansmana Hazırlık Checklist

### 23.1 Teknik
- ☐ Tüm sayfalarda CSRF token
- ☐ Server-side validation
- ☐ Lighthouse skoru > 85
- ☐ Mobile test: 360px, 768px, 1024px
- ☐ Hata sayfaları (404, 500) Türkçe
- ☐ Sitemap.xml + robots.txt
- ☐ Google Analytics 4 + Search Console
- ☐ SSL Labs test → A+
- ☐ Backup test (DB restore)
- ☐ Admin 2FA aktif
- ☐ Rate limiting kritik endpointlerde
- ☐ Sentry hata takibi aktif

### 23.2 Yasal
- ☐ KVKK aydınlatma metni
- ☐ Çerez izni banner çalışıyor
- ☐ Mesafeli satış sözleşmesi
- ☐ Kullanım koşulları
- ☐ Yemekçi sözleşmesi
- ☐ Avukat review tamamlanmış
- ☐ Mali müşavir review tamamlanmış

### 23.3 İş
- ☐ Pilot yemekçi listesi (10-15 firma)
- ☐ Pilot müşteri listesi (5-10 fabrika)
- ☐ İlk 6 ay sıfır komisyon kampanyası tanımlı
- ☐ Onboarding süreci dokümante
- ☐ Müşteri hizmetleri telefonu aktif
- ☐ WhatsApp Business hesabı
- ☐ Sosyal medya hesapları açık

### 23.4 Pazarlama
- ☐ Logo final
- ☐ Marka kimlik rehberi
- ☐ Tanıtım videosu (1-2 dk)
- ☐ Müşteri logoları izinli ve hazır
- ☐ İlk basın bülteni
- ☐ MÜSİAD üye iletişim listesi
- ☐ LinkedIn lansman post serisi
- ☐ Google Ads kampanyası taslak

---

## 📌 Doküman Sonu

Bu PRD, Yemekhaneci.com.tr platformunun MVP lansmanına 5-6 ayda ulaşması için yeterli detayı içerir. Claude Code geliştirme süreçlerinde **'tek doğru kaynak'** olarak kullanılır.

**İlgili dokümanlar:**
- `02_Kurallar/CLAUDE.md` — Kod kuralları
- `02_Kurallar/CLAUDE_CODE_KOMUTLARI.md` — Faz bazlı hazır komutlar
- `03_Demo_HTML/` — UI/UX davranış referansları
- `04_Claude_Code_Komutlar/` — Başlangıç komutları
- `05_Hukuki/` — Sözleşme şablonları
- `06_Marka/` — Logo brief
- `07_Pilot/` — Onboarding rehberi
- `08_Veritabani/` — SQL şema

---

*© 2026 UYSA Yemek Hizmetleri — Yemekhaneci.com.tr*

# PRD Bölüm 25 — Enflasyon Hesaplayıcı

> **PRD v3.0 eki — v3.1 olarak yayınlanacak**
> Onay: ADR-009 (revize), ADR-013b
> Tarih: 2026-05-08

---

## 25.1 Amaç ve Konumlanma

Yemekhaneci.com.tr içinde **tarihsel fiyat-bugün karşılığı** hesaplaması yapan tek araç. Üç katman:

1. **Müşteri tarafı (anasayfa aracı):** SEO + lead capture motoru. "2024 Mart'ta 250 ₺ olan menü bugün ne olur?" sorusunu Türkçe ve hızlı yanıtlar.
2. **Yemekçi paneli:** Kendi maliyet/menü fiyatlarını güncellerken endeks bazlı zam önerisi.
3. **Admin paneli:** Sektör analiz aracı + komisyon planlama + **özel UYSA endeksleri** (Et / Sebze / Süt vb.) oluşturup yönetme.

## 25.2 Kullanıcı Hikayeleri

| Aktör | Hikaye | Kabul Ölçütü |
|-------|--------|--------------|
| Müşteri | "2024 Ocak'ta 5.000 ₺ ödediğim catering bugün ne olur?" | 30 saniyeden kısa, 4 endeks seçeneği, sonuç + grafik. |
| Yemekçi | "Maliyet matrisimi son 12 ayın gıda enflasyonuna göre güncellemek istiyorum." | Tek tık öneri: "Gıda endeksi son 12 ayda %X arttı, fiyatlarınızı %X artırın". |
| Admin | "UYSA Et Endeksi yarat, geçmiş 24 ayın değerini elle gir." | CRUD form, FY validasyon, audit log. |
| Admin | "Bu ay TÜİK verisi çekildi mi?" | Job dashboard, başarı/başarısız + son çekim zamanı. |
| KVKK Sorumlusu | "Lead capture'larda kim ne kabul etmiş?" | Audit log, IP, UA, onay tarihi sorgulanabilir. |

## 25.3 Veri Modeli

### 25.3.1 `inflation_sources`

Endeks tanım kataloğu. Hem resmî hem özel formüller burada saklanır.

| Sütun | Tip | Açıklama |
|-------|-----|----------|
| `id` | `BIGINT UNSIGNED PK` | |
| `code` | `VARCHAR(64) UNIQUE` | `tuik_tufe`, `tuik_tufe_gida`, `tuik_yiufe`, `enag_tufe`, `uysa_et_endeksi`, ... |
| `name` | `VARCHAR(150)` | Görünür ad: "TÜİK TÜFE Genel", "UYSA Et Endeksi" |
| `description` | `TEXT NULL` | Sayfa altındaki açıklama metni |
| `source_type` | `ENUM('tuik_api','enag_manual','custom_admin')` | Veri akışı tipi |
| `tuik_evds_code` | `VARCHAR(64) NULL` | Sadece `tuik_api` için: `TP.FG.J0` vb. |
| `base_period` | `VARCHAR(10) NULL` | "2003=100", "2010=100" |
| `unit` | `VARCHAR(20) DEFAULT 'index'` | Genelde `index`; özel formüller için `tl_kg`, `tl_lt` olabilir |
| `is_official` | `TINYINT(1) DEFAULT 0` | Resmî mi (TÜİK/ENAG) |
| `is_active` | `TINYINT(1) DEFAULT 1` | Müşteri tarafına gösterilsin mi |
| `display_order` | `INT DEFAULT 100` | Sıralama (resmî kaynaklar önce) |
| `color_hex` | `VARCHAR(7) NULL` | Grafik rengi |
| `created_by_admin_id` | `BIGINT UNSIGNED NULL FK users(id)` | Özel formüllerde kim oluşturdu |
| `created_at`, `updated_at` | `TIMESTAMP` | |

### 25.3.2 `inflation_indices`

Aylık endeks değerleri. Bir kaynak için ay başına bir kayıt.

| Sütun | Tip | Açıklama |
|-------|-----|----------|
| `id` | `BIGINT UNSIGNED PK` | |
| `source_id` | `BIGINT UNSIGNED FK inflation_sources(id) ON DELETE CASCADE` | |
| `period_year` | `SMALLINT UNSIGNED` | 2003-2099 |
| `period_month` | `TINYINT UNSIGNED` | 1-12 |
| `index_value` | `DECIMAL(14,4)` | Baz dönem = 100 (resmî); özel için ham değer |
| `monthly_change_pct` | `DECIMAL(7,4) NULL` | Bir önceki aya göre |
| `yearly_change_pct` | `DECIMAL(7,4) NULL` | 12 ay önceye göre |
| `fetched_at` | `TIMESTAMP NULL` | API ile geldi mi |
| `entered_by_admin_id` | `BIGINT UNSIGNED NULL FK users(id)` | Manuel girildiyse |
| `source_url` | `VARCHAR(500) NULL` | Resmî yayın URL'si |
| `notes` | `VARCHAR(500) NULL` | Yorum (örn. "Yeniden hesaplanmış değer") |
| `created_at`, `updated_at` | `TIMESTAMP` | |
| Tekil anahtar | `UNIQUE (source_id, period_year, period_month)` | |
| İndeks | `(period_year, period_month)` | |

### 25.3.3 `inflation_calculations`

Anonim/bağlı sorgular. Lead capture + analytics + KVKK kayıt.

| Sütun | Tip | Açıklama |
|-------|-----|----------|
| `id` | `BIGINT UNSIGNED PK` | |
| `source_id` | `BIGINT UNSIGNED FK inflation_sources(id)` | |
| `start_date` | `DATE` | Ay-hassas |
| `start_price` | `DECIMAL(14,2)` | |
| `end_date` | `DATE` | |
| `end_price` | `DECIMAL(14,2)` | Hesaplanmış sonuç |
| `change_pct` | `DECIMAL(8,4)` | Toplam yüzde değişim |
| `email` | `VARCHAR(255) NULL` | Lead capture'da |
| `kvkk_accepted_at` | `TIMESTAMP NULL` | Onay zaman damgası |
| `ip_address` | `VARBINARY(16) NOT NULL` | IPv4/v6 |
| `user_agent` | `VARCHAR(500) NULL` | |
| `panel_origin` | `ENUM('public','supplier','admin')` | Hangi panelden |
| `created_at` | `TIMESTAMP DEFAULT CURRENT_TIMESTAMP` | |
| İndeks | `(email)`, `(created_at)`, `(panel_origin)` | |

## 25.4 Hesaplama Formülü

```
end_index   = inflation_indices(source_id, end_year, end_month).index_value
start_index = inflation_indices(source_id, start_year, start_month).index_value
end_price   = round(start_price × (end_index / start_index), 2)
change_pct  = round(((end_index - start_index) / start_index) × 100, 4)
```

**Eksik veri davranışı:** Hedef ay henüz açıklanmamışsa (örn. mayıs verisi nisanın 5'inde gelir), kullanıcıya en son açıklanan ay sonucu ve "Bu ay henüz açıklanmadı, X ayı verisi kullanıldı" notu gösterilir.

## 25.5 EVDS API Entegrasyonu

- **Endpoint:** `https://evds2.tcmb.gov.tr/service/evds/series={code}&startDate={dd-MM-yyyy}&endDate={dd-MM-yyyy}&type=json`
- **Auth:** API anahtarı `key` query param'ıyla.
- **Endeks kodları (Faz 0.5 başında doğrulanacak):**
  - TÜFE genel: `TP.FG.J0`
  - TÜFE gıda ve alkolsüz içecekler: `TP.FG.J01`
  - Yİ-ÜFE genel: `TP.FE.OKTG01`
- **Rate limit:** EVDS bilinen sınırlar (Faz 0.5.4 başvurusunda netleşecek). Servis tarafında ayrıca 60/dakika tampon.
- **Cron:** Her ayın 5. günü 03:00 (TR) — bir önceki ayın endeksini çek. Tekrar çalışırsa idempotent (UNIQUE anahtar nedeniyle UPSERT).

## 25.6 UI/UX

### 25.6.1 Müşteri Sayfası (`/araclar/enflasyon-hesaplayici`)

- **Hero:** "2024'te aldığım catering bugün ne olur?" sorusu + form.
- **Form alanları:**
  - Başlangıç tarihi (ay-hassas datepicker)
  - Başlangıç fiyatı (₺ input, IMask ile binlik ayraç)
  - Hedef tarih (varsayılan = bu ay)
  - Endeks (radio: 4 resmî kaynak; aktif özel kaynaklar admin tarafından gösterilirse)
- **Sonuç kartı:**
  - Büyük rakam: "₺ XXX,XX"
  - Yüzde değişim
  - Chart.js ile aylık endeks grafiği (start → end)
  - "E-postama gönder" CTA → KVKK onaylı lead capture
- **SEO:** Türkçe slug, meta description, schema.org `Article` + `WebApplication`.
- **A11y:** Tüm input'lara `<label>`, klavye erişilebilir, kontrast WCAG AA.

### 25.6.2 Yemekçi Sayfası (`/yemekci/araclar/enflasyon`)

Müşteri sayfasının üst kısmı + ek özellikler:

- "Maliyet matrisimi güncelle" CTA → Faz 2'deki maliyet sayfasına ön-doldurma.
- "Son 12 ay özet": her resmî kaynak için yıllık % değişim kartı.

### 25.6.3 Admin Sayfası (`/yonetim/araclar/enflasyon` + `/yonetim/sistem/enflasyon-kaynaklari`)

- Hesaplayıcı (yemekçi versiyonuyla aynı) +
- **Kaynak yönetim ekranı:**
  - Liste: tüm kaynaklar (resmî + özel)
  - "Yeni Kaynak" butonu → form: `code`, `name`, `description`, `source_type=custom_admin`, `unit`, `color_hex`
  - Kaynak detay: aylık veri tablosu + "Yeni Ay Ekle" formu
  - "Toplu CSV İçe Aktar" — geçmiş veriler için
- **EVDS Job durumu:** son çalışma zamanı, başarı/hata sayısı, manuel tetikleme butonu.
- **Audit log:** her kaynak/değer değişikliği `activity_log`'a yansır.

## 25.7 Endpoint'ler

| Yöntem | URL | Yetki | Açıklama |
|--------|-----|:-----:|----------|
| `GET` | `/araclar/enflasyon-hesaplayici` | Public | Müşteri sayfası |
| `GET` | `/yemekci/araclar/enflasyon` | Yemekçi | Yemekçi sayfası |
| `GET` | `/yonetim/araclar/enflasyon` | Admin | Admin sayfası |
| `GET` | `/yonetim/sistem/enflasyon-kaynaklari` | Admin | Kaynak listesi |
| `POST` | `/yonetim/sistem/enflasyon-kaynaklari` | Admin | Yeni özel kaynak |
| `PUT` | `/yonetim/sistem/enflasyon-kaynaklari/{id}` | Admin | Düzenle |
| `DELETE` | `/yonetim/sistem/enflasyon-kaynaklari/{id}` | Admin | Pasifleştir (soft delete) |
| `POST` | `/yonetim/sistem/enflasyon-kaynaklari/{id}/aylik-veri` | Admin | Yeni aylık değer |
| `POST` | `/yonetim/sistem/enflasyon-kaynaklari/{id}/csv-import` | Admin | CSV toplu içe aktarım |
| `GET` | `/api/v1/enflasyon/kaynaklar` | Public | Aktif kaynak listesi |
| `POST` | `/api/v1/enflasyon/hesapla` | Public | Hesaplama API |
| `POST` | `/api/v1/enflasyon/mail-gonder` | Public | Lead capture |
| `POST` | `/api/v1/admin/enflasyon/evds-tetikle` | Admin | EVDS job manuel tetikle |

## 25.8 Güvenlik ve KVKK

- Lead capture'da KVKK onayı zorunlu; onay zamanı, IP, UA `inflation_calculations` tablosunda.
- E-posta `inflation_calculations.email` alanında saklanır; pazarlama gönderimleri için ayrı `marketing_consent` tablosu (Faz 4'te).
- Public endpoint'ler rate limit'li: `/api/v1/enflasyon/hesapla` IP başı 30/dakika; `/api/v1/enflasyon/mail-gonder` IP başı 5/saat.
- Admin formülleri `before_value`/`after_value` ile audit log'a yansır.
- EVDS API anahtarı `.env`'de (`EVDS_API_KEY`), repo'da yok.

## 25.9 Test Stratejisi

- **Unit:** `InflationCalculator::calculate()` — formül doğruluğu, kenar durumlar (aynı tarih, eksik veri, ileri tarih).
- **Feature:** Her endpoint için happy path + 422 (validation) + 429 (rate limit).
- **Manuel:** Demo verisiyle 3 panelde de form testi.
- **Cron:** EVDS job'unu test ortamında manuel tetikle, idempotent olduğunu doğrula.
- **Hesap doğruluğu:** Bağımsız Excel hesabı ile karşılaştırma (3 örnek).

## 25.10 Faz 0.5 Çıkış Kriterleri

1. 3 panelde de hesaplayıcı çalışıyor.
2. 4 resmî kaynak EVDS'den otomatik geliyor (en az son 24 ay).
3. Admin yeni özel kaynak oluşturup veri girebiliyor.
4. Lead capture KVKK uyumlu çalışıyor.
5. Test kapsamı %80+ (formül + endpoint).
6. Lighthouse public sayfa skoru ≥ 90 (performance).
7. Production'a değil, **staging**'e deploy edildi (Faz 1.0c'den sonra production).

---

**İlgili belgeler:**
- ADR-009 (kapsamı belirleyen karar)
- ADR-013b (3 panel + özel formül kararı)
- ADR-013c (paralel strateji)
- `database_schema.sql` (mevcut 28 tablo, bu 3 yeni tabloyla 31 tablo olur)

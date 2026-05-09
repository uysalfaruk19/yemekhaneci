<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Auth\SimpleAuth;

/**
 * Henüz tamamlanmamış admin modülleri için placeholder sayfalar.
 * Her sayfa modül kapsamını + planlanan fazı + örnek görseli gösterir.
 *
 * Faz N tamamlandıkça ilgili metod gerçek controller'a taşınır.
 */
final class PlaceholderController
{
    public function dashboardKpi(): string       { return $this->render('Dashboard (KPI + Grafik + Aktivite)', 'Faz 1+', 'bi-speedometer2', [
        ['Aylık GMV grafiği (son 12 ay) — Chart.js line'],
        ['Sipariş hacmi heatmap (gün × saat)'],
        ['Yemekçi performans tablosu (top-10 / bottom-10)'],
        ['Bölge bazlı talep haritası (Leaflet.js + Türkiye il poligonu)'],
        ['Komisyon geliri kategoriye göre pie chart'],
    ]); }

    public function liveActivity(): string       { return $this->render('Canlı Aktivite Akışı', 'Faz 1+', 'bi-broadcast', [
        ['WebSocket veya 5 sn polling ile son işlemler'],
        ['Yeni talep / yeni sipariş / ödeme alındı / iptal akışı'],
        ['Sesli uyarı (kritik anlaşmazlık)'],
        ['Filtre: tip, tutar üstü, bölge'],
    ]); }

    public function kycReview(): string          { return $this->render('Yemekçi Onayları (KYC)', 'Faz 1', 'bi-clipboard-check', [
        ['5 belge inceleme ekranı: vergi levhası, faaliyet, sağlık, hijyen, sertifika'],
        ['PDF/JPG önizleme (ImageMagick)'],
        ['Onay/red butonu + sebep textarea'],
        ['Otomatik e-posta + SMS bildirimi'],
        ['Şu an temel onay /yonetim/yemekciler liste sayfasında mevcut'],
    ]); }

    public function userManagement(): string     { return $this->render('Kullanıcı Yönetimi', 'Faz 1', 'bi-people-fill', [
        ['4 user_type: customer / supplier / admin / super_admin'],
        ['Liste + filtre + arama (e-posta, isim, firma)'],
        ['Detay: KYC bilgileri, sipariş geçmişi, audit log'],
        ['Aksiyonlar: askıya al, parola sıfırla, KVKK silme talebi'],
        ['KVKK uyumlu silme: 30 gün anonimleştirme süresi'],
    ]); }

    public function ordersTracking(): string     { return $this->render('Sipariş & Teklif Takibi', 'Faz 6', 'bi-receipt-cutoff', [
        ['Filtre: durum, tarih, yemekçi, müşteri, tutar'],
        ['Sipariş timeline: oluşturuldu → ödendi → kabul → hazırlık → teslim'],
        ['Müdahale: durum güncelle, iade başlat, not ekle'],
        ['Toplu işlem (bulk export Excel)'],
    ]); }

    public function quotesPivot(): string        { return $this->render('Teklif Pivot (Yemekçi × Müşteri)', 'Faz 3.5', 'bi-grid-3x3', [
        ['3 boyutlu pivot: yemekçi × müşteri × dönem'],
        ['Hücreler: teklif sayısı, kabul oranı, ortalama tutar'],
        ['Dönüşüm hunisi: talep → teklif → kabul → sipariş'],
        ['CSV export'],
    ]); }

    public function disputes(): string           { return $this->render('Anlaşmazlık Yönetimi', 'Faz 7', 'bi-exclamation-octagon', [
        ['Vaka detay: müşteri vs yemekçi mesajları'],
        ['Mediasyon notları (admin internal)'],
        ['Müşteri lehine iade onay (Iyzico refund API)'],
        ['Yemekçi puan etkisi (-0.5 / -1.0)'],
        ['Tahkim eskalasyonu (İstanbul Tahkim Merkezi)'],
    ]); }

    public function financial(): string          { return $this->render('Finansal Yönetim', 'Faz 6', 'bi-cash-stack', [
        ['Komisyon hesabı: sipariş × oran (%5-10)'],
        ['Yemekçi alacak/borç bakiyesi (cari hesap)'],
        ['Haftalık ödeme talimatı (banka transferi)'],
        ['E-fatura entegrasyonu (ParamPos / Foriba)'],
        ['KDV %10 hesaplama + dönemsel rapor'],
        ['Stopaj kontrolü (gerçek kişi yemekçi varsa)'],
    ]); }

    public function contentModeration(): string  { return $this->render('İçerik Moderasyonu', 'Faz 5', 'bi-shield-check', [
        ['Menü onay kuyruğu (yemekçi yeni menü ekledi)'],
        ['Görsel kontrolü (uygunsuz içerik tespiti)'],
        ['Müşteri yorum onay/red (küfür/spam filter)'],
        ['Yorum yanıtı için yemekçiye bildirim'],
    ]); }

    public function marketing(): string          { return $this->render('Pazarlama Araçları', 'Faz 4+', 'bi-megaphone', [
        ['Kupon kodu üreteci (sabit % indirim, ilk sipariş, vb.)'],
        ['Kampanya planlama (banner + landing page)'],
        ['Toplu e-posta gönderimi (Brevo segment)'],
        ['SMS toplu (Netgsm — opt-in liste)'],
        ['A/B test altyapısı (header, CTA, fiyat)'],
    ]); }

    public function systemSettings(): string     { return $this->render('Sistem Ayarları', 'Faz 1', 'bi-gear', [
        ['Komisyon oranları (segment × kategori matrisi)'],
        ['Kategori yönetimi (yemek tipi, mutfak)'],
        ['API anahtarları (.env editörü, sadece super_admin)'],
        ['SMS / mail gönderici şablon yönetimi'],
        ['Sistem bakım modu (planlı duraksatma)'],
    ]); }

    private function render(string $title, string $phase, string $icon, array $features): string
    {
        $content = \view('admin.placeholder', [
            'page_title' => $title,
            'phase'      => $phase,
            'icon'       => $icon,
            'features'   => $features,
        ]);
        return \layout('app', $content, [
            'title'  => $title . ' — Yönetim',
            'authed' => true,
            'user'   => SimpleAuth::user(),
        ]);
    }
}

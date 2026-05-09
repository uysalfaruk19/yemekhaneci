<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Auth\SimpleAuth;

/**
 * Müşteri ve yemekçi tarafı tanıtım sayfaları (PRD §6).
 * Faz 0.5'te statik içerik; Faz 5'te dinamik (yemekçi DB'den, content management).
 */
final class MarketingController
{
    public function topluYemek(): string
    {
        $content = \view('marketing.toplu-yemek');
        return \layout('app', $content, [
            'title' => 'Toplu Yemek Hizmeti — Yemekhaneci',
            'description' => 'Fabrika, ofis, okul ve yurtlar için toplu yemek tedarikçileri. Şeffaf fiyat, anonim eşleşme, KVKK uyum.',
            'authed' => SimpleAuth::check(),
            'user'   => SimpleAuth::user(),
        ]);
    }

    public function yemekciler(): string
    {
        $content = \view('marketing.yemekciler');
        return \layout('app', $content, [
            'title' => 'Onaylı Yemekçi Firmalar — Yemekhaneci',
            'description' => 'KYC denetiminden geçmiş, sağlık ve hijyen sertifikalı yemekçi firmalar. Anonim isim + lokasyon + puan.',
            'authed' => SimpleAuth::check(),
            'user'   => SimpleAuth::user(),
        ]);
    }

    public function nasilCalisir(): string
    {
        $content = \view('marketing.nasil-calisir');
        return \layout('app', $content, [
            'title' => 'Nasıl Çalışır — Yemekhaneci',
            'description' => 'Yemekhaneci üzerinden yemek tedarik süreci 5 adım: talep → anonim teklif → karşılaştırma → seçim → teslim.',
            'authed' => SimpleAuth::check(),
            'user'   => SimpleAuth::user(),
        ]);
    }
}

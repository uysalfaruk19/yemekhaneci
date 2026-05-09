<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Auth\SimpleAuth;

/**
 * Yasal sayfalar — KVKK, çerez, kullanım koşulları (Faz 0.5 P0).
 * Şablonlar UYSA hukuk danışmanı tarafından gözden geçirildikten sonra final olur.
 */
final class LegalController
{
    public function privacyNotice(): string
    {
        $content = \view('legal.privacy-notice');
        return \layout('app', $content, [
            'title' => 'KVKK Aydınlatma Metni — Yemekhaneci',
            'description' => 'Yemekhaneci.com.tr (UYSA Yemek Hizmetleri) kişisel verilerinizi nasıl işler — KVKK Madde 10 kapsamında aydınlatma.',
            'authed' => SimpleAuth::check(),
            'user' => SimpleAuth::user(),
        ]);
    }

    public function cookiePolicy(): string
    {
        $content = \view('legal.cookie-policy');
        return \layout('app', $content, [
            'title' => 'Çerez Politikası — Yemekhaneci',
            'description' => 'Yemekhaneci.com.tr çerez kullanımı, kategoriler ve opt-in tercihleri.',
            'authed' => SimpleAuth::check(),
            'user' => SimpleAuth::user(),
        ]);
    }

    public function termsOfService(): string
    {
        $content = \view('legal.terms');
        return \layout('app', $content, [
            'title' => 'Kullanım Koşulları — Yemekhaneci',
            'authed' => SimpleAuth::check(),
            'user' => SimpleAuth::user(),
        ]);
    }

    public function dataDeletion(): string
    {
        $content = \view('legal.data-deletion');
        return \layout('app', $content, [
            'title' => 'Veri Silme Talebi — Yemekhaneci',
            'description' => 'KVKK Madde 11 kapsamındaki haklarınız: silme, düzeltme, taşınabilirlik talebi.',
            'authed' => SimpleAuth::check(),
            'user' => SimpleAuth::user(),
        ]);
    }
}

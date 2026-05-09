<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Repositories\QuickQuoteRepository;
use App\Services\AuditLogger;
use App\Services\RateLimiter;

/**
 * Hızlı Teklif — anasayfa wizard 9 soruluk akış (PRD §7.2).
 *
 * 1. Kişi sayısı
 * 2. Öğün dağılımı (multi: ogle/aksam/kumanya, her biri için kişi sayısı)
 * 3. Menü yapısı (chip seçimleri)
 * 4. Hizmet segmenti (ekonomik/genel/premium)
 * 5. Hizmet lokasyonu (şehir/ilçe/adres)
 * 6. Personel desteği (toggle + alt sayılar)
 * 7. Mutfak yatırımı (toggle + 8 ekipman)
 * 8. Cumartesi çalışma (yes/no/partial)
 * 9. Notlar (etiket + serbest metin) + iletişim + KVKK
 */
final class QuickQuoteController
{
    private const MEAL_KEYS = ['ogle', 'aksam', 'kumanya'];
    private const SEGMENTS = ['ekonomik', 'genel', 'premium'];
    private const SATURDAY = ['no', 'yes', 'partial'];
    private const EQUIPMENT_ITEMS = [
        'ocak', 'firin', 'bulasik', 'sogutucu', 'depo', 'davlumbaz', 'salon', 'self_servis',
    ];

    public function submit(): string
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            return self::json(['success' => false, 'error' => 'CSRF', 'message' => 'Oturum doğrulaması başarısız.'], 419);
        }

        $rl = new RateLimiter('quick_quote', limit: 10, windowSeconds: 3600);
        $ip = InflationCalculatorController::clientIp();
        if (!$rl->allow($ip)) {
            header('Retry-After: ' . $rl->retryAfter($ip));
            return self::json([
                'success'     => false,
                'error'       => 'RATE_LIMITED',
                'message'     => 'Saatlik talep limitiniz doldu. Lütfen 1 saat sonra tekrar deneyin.',
                'retry_after' => $rl->retryAfter($ip),
            ], 429);
        }

        $errors = [];

        // S1: Kişi sayısı
        $guestCount = (int) ($_POST['guest_count'] ?? 0);
        if ($guestCount < 1 || $guestCount > 10000) {
            $errors['guest_count'] = ['Kişi sayısı 1 ile 10.000 arasında olmalı.'];
        }

        // S2: Öğün dağılımı
        $meals = [];
        $totalMeal = 0;
        foreach (self::MEAL_KEYS as $key) {
            $count = (int) ($_POST['meals'][$key] ?? 0);
            if ($count < 0 || $count > 10000) {
                $errors['meals'] = ['Öğün başı kişi sayıları 0-10.000 arasında olmalı.'];
                break;
            }
            $meals[$key] = $count;
            $totalMeal += $count;
        }
        if ($totalMeal === 0) {
            $errors['meals'] = ['En az bir öğün için kişi sayısı girmelisiniz.'];
        }

        // S3: Menü
        $menu = [
            'soup'             => !empty($_POST['menu']['soup']),
            'main_dish'        => !empty($_POST['menu']['main_dish']),
            'side_dish'        => !empty($_POST['menu']['side_dish']),
            'bread'            => !empty($_POST['menu']['bread']),
            'salad_bar_count'  => max(0, min(7, (int) ($_POST['menu']['salad_bar_count'] ?? 0))),
            'dessert'          => in_array((string) ($_POST['menu']['dessert'] ?? 'none'), ['none', 'fruit', 'dessert'], true)
                                    ? (string) $_POST['menu']['dessert'] : 'none',
            'drinks'           => in_array((string) ($_POST['menu']['drinks'] ?? 'rotation'),
                                    ['ayran', 'yogurt', 'rotation', 'both'], true)
                                    ? (string) $_POST['menu']['drinks'] : 'rotation',
        ];

        // S4: Segment
        $segment = (string) ($_POST['segment'] ?? 'genel');
        if (!in_array($segment, self::SEGMENTS, true)) {
            $errors['segment'] = ['Geçersiz segment seçimi.'];
        }

        // S5: Lokasyon
        $city = trim((string) ($_POST['location']['city'] ?? ''));
        if (mb_strlen($city) < 2 || mb_strlen($city) > 80) {
            $errors['city'] = ['Şehir 2-80 karakter olmalı.'];
        }
        $location = [
            'city'     => $city,
            'district' => trim((string) ($_POST['location']['district'] ?? '')),
            'address'  => trim((string) ($_POST['location']['address'] ?? '')),
        ];

        // S6: Personel
        $personnel = ['enabled' => !empty($_POST['personnel']['enabled'])];
        if ($personnel['enabled']) {
            $personnel['cooks']    = max(0, min(20, (int) ($_POST['personnel']['cooks']    ?? 0)));
            $personnel['service']  = max(0, min(50, (int) ($_POST['personnel']['service']  ?? 0)));
            $personnel['cleaning'] = max(0, min(20, (int) ($_POST['personnel']['cleaning'] ?? 0)));
        }

        // S7: Ekipman
        $equipment = ['enabled' => !empty($_POST['equipment']['enabled']), 'items' => []];
        if ($equipment['enabled']) {
            $items = $_POST['equipment']['items'] ?? [];
            if (is_array($items)) {
                $equipment['items'] = array_values(array_intersect(self::EQUIPMENT_ITEMS, $items));
            }
        }

        // S8: Cumartesi
        $saturday = (string) ($_POST['saturday'] ?? 'no');
        if (!in_array($saturday, self::SATURDAY, true)) {
            $errors['saturday'] = ['Geçersiz cumartesi seçimi.'];
        }

        // S9: Notlar + iletişim
        $tags = $_POST['notes']['tags'] ?? [];
        $notes = [
            'tags' => is_array($tags) ? array_values(array_filter(array_map('strval', $tags))) : [],
            'text' => mb_substr(trim((string) ($_POST['notes']['text'] ?? '')), 0, 1000),
        ];

        $contactName  = trim((string) ($_POST['contact_name'] ?? ''));
        $contactEmail = trim((string) ($_POST['contact_email'] ?? ''));
        $contactPhone = trim((string) ($_POST['contact_phone'] ?? ''));

        if ($contactEmail === '' && $contactPhone === '') {
            $errors['contact'] = ['Size dönüş yapabilmemiz için e-posta veya telefon girin.'];
        }
        if ($contactEmail !== '' && !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'] = ['Geçerli bir e-posta giriniz.'];
        }
        if ($contactPhone !== '' && !preg_match('/^[\d\s+()-]{7,25}$/', $contactPhone)) {
            $errors['contact_phone'] = ['Geçerli bir telefon numarası giriniz.'];
        }
        if (empty($_POST['kvkk'])) {
            $errors['kvkk'] = ['KVKK onayı zorunludur.'];
        }

        if ($errors) {
            return self::json([
                'success' => false,
                'error'   => 'VALIDATION_FAILED',
                'errors'  => $errors,
                'message' => 'Lütfen formu kontrol edin.',
            ], 422);
        }

        $record = (new QuickQuoteRepository())->create([
            'guest_count'   => $guestCount,
            'meals'         => $meals,
            'menu'          => $menu,
            'segment'       => $segment,
            'location'      => $location,
            'personnel'     => $personnel,
            'equipment'     => $equipment,
            'saturday'      => $saturday,
            'notes'         => $notes,
            'contact_name'  => $contactName,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'kvkk'          => true,
            'ip_address'    => $ip,
            'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        AuditLogger::log('quote.submitted', [
            'reference' => $record['reference'],
            'guests'    => $guestCount,
            'meals'     => $meals,
            'city'      => $city,
            'segment'   => $segment,
        ]);

        return self::json([
            'success' => true,
            'data'    => [
                'reference'  => $record['reference'],
                'message'    => 'Talebiniz alındı. Onaylı yemekçilerden teklifler 24 saat içinde geliyor olacak.',
                'next_steps' => [
                    '24 saat içinde 3-5 yemekçinin tekliflerini e-posta/SMS ile alacaksınız.',
                    'Anonim listeyi inceleyin, beğendiğiniz yemekçinin profilini açın.',
                    'Mesajlaşma ve sözleşme platform üzerinden devam edecek.',
                ],
            ],
            'message' => 'Talebiniz alındı.',
        ]);
    }

    private static function json(array $data, int $status = 200): string
    {
        http_response_code($status);
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}';
    }
}

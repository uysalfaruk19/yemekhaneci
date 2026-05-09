<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Repositories\QuickQuoteRepository;
use App\Repositories\SupplierApplicationRepository;
use App\Services\AuditLogger;
use App\Services\RateLimiter;

/**
 * Hızlı Teklif — anasayfa wizard 9 soruluk akış (PRD §7.2).
 *
 * 1. Günlük kaç öğün? (1/2/3)
 * 2. Kaç kişilik? (1-10000)
 * 3. Menü yapısı (sabit + salata bar + tatlı/meyve dönüşüm + ayran/yoğurt)
 * 4. Hizmet segmenti (ekonomik/genel/premium)
 * 5. Hizmet lokasyonu
 * 6. Personel desteği (Var / Yok kartı + alt sayılar)
 * 7. Ekipman (mevcut + talep edilen — 2 textarea)
 * 8. Cumartesi çalışma
 * 9. Notlar (etiket + serbest metin) → Fiyat Öğren
 *
 * Sonuç: anonim 3-5 yemekçi + ortalama fiyat. Detaylı teklif iletişim formu opsiyonel.
 */
final class QuickQuoteController
{
    private const SEGMENTS = ['ekonomik', 'genel', 'premium'];
    private const SATURDAY = ['no', 'yes', 'partial'];

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

        $mealCount = (int) ($_POST['meal_count'] ?? 0);
        if (!in_array($mealCount, [1, 2, 3], true)) {
            $errors['meal_count'] = ['Günlük öğün sayısı 1, 2 veya 3 olmalı.'];
        }

        $guestCount = (int) ($_POST['guest_count'] ?? 0);
        if ($guestCount < 1 || $guestCount > 10000) {
            $errors['guest_count'] = ['Kişi sayısı 1 ile 10.000 arasında olmalı.'];
        }

        $meals = self::deriveMeals($mealCount, $guestCount);

        $menu = [
            'soup'             => !empty($_POST['menu']['soup']),
            'main_dish'        => !empty($_POST['menu']['main_dish']),
            'side_dish'        => !empty($_POST['menu']['side_dish']),
            'bread'            => !empty($_POST['menu']['bread']),
            'salad_bar_count'  => max(0, min(7, (int) ($_POST['menu']['salad_bar_count'] ?? 0))),
            'dessert_rotation' => !empty($_POST['menu']['dessert_rotation']),
            'drinks'           => in_array((string) ($_POST['menu']['drinks'] ?? 'rotation'),
                                    ['ayran', 'yogurt', 'rotation', 'both'], true)
                                    ? (string) $_POST['menu']['drinks'] : 'rotation',
        ];

        $segment = (string) ($_POST['segment'] ?? 'genel');
        if (!in_array($segment, self::SEGMENTS, true)) {
            $errors['segment'] = ['Geçersiz segment seçimi.'];
        }

        $city = trim((string) ($_POST['location']['city'] ?? ''));
        if (mb_strlen($city) < 2 || mb_strlen($city) > 80) {
            $errors['city'] = ['Şehir 2-80 karakter olmalı.'];
        }
        $location = [
            'city'     => $city,
            'district' => trim((string) ($_POST['location']['district'] ?? '')),
            'address'  => trim((string) ($_POST['location']['address'] ?? '')),
        ];

        $personnel = ['enabled' => !empty($_POST['personnel']['enabled'])];
        if ($personnel['enabled']) {
            $personnel['cooks']    = max(0, min(20, (int) ($_POST['personnel']['cooks']    ?? 0)));
            $personnel['service']  = max(0, min(50, (int) ($_POST['personnel']['service']  ?? 0)));
            $personnel['cleaning'] = max(0, min(20, (int) ($_POST['personnel']['cleaning'] ?? 0)));
        }

        $equipment = [
            'has_existing' => mb_substr(trim((string) ($_POST['equipment']['has_existing'] ?? '')), 0, 1000),
            'requested'    => mb_substr(trim((string) ($_POST['equipment']['requested']    ?? '')), 0, 1000),
        ];

        $saturday = (string) ($_POST['saturday'] ?? 'no');
        if (!in_array($saturday, self::SATURDAY, true)) {
            $errors['saturday'] = ['Geçersiz cumartesi seçimi.'];
        }

        $tags = $_POST['notes']['tags'] ?? [];
        $notes = [
            'tags' => is_array($tags) ? array_values(array_filter(array_map('strval', $tags))) : [],
            'text' => mb_substr(trim((string) ($_POST['notes']['text'] ?? '')), 0, 1000),
        ];

        if ($errors) {
            return self::json([
                'success' => false,
                'error'   => 'VALIDATION_FAILED',
                'errors'  => $errors,
                'message' => 'Lütfen formu kontrol edin.',
            ], 422);
        }

        // Aktif yemekçileri çek — varsa gerçek fiyatlardan listele, yoksa mock
        $supplierRepo = new SupplierApplicationRepository();
        $activeSuppliers = $supplierRepo->activeForCity($location['city'], 5);

        if (count($activeSuppliers) > 0) {
            $supplierList = self::buildAnonymousFromReal(
                $activeSuppliers, $segment, $menu, $personnel, $equipment, $saturday
            );
            // Ortalama fiyat = aktif yemekçilerin ortalaması
            $avgPerMeal = (int) round(array_sum(array_column($supplierList, 'price_per_meal')) / count($supplierList));
            $pricing = ['per_meal' => $avgPerMeal];
        } else {
            $pricing = self::estimatePricing($segment, $menu, $personnel, $equipment, $saturday);
            $supplierList = self::anonymousSuppliers($pricing['per_meal'], $location['city']);
        }
        $monthly = self::monthlyTotal($pricing['per_meal'], $guestCount, $mealCount, $saturday);

        $record = (new QuickQuoteRepository())->create([
            'meal_count'    => $mealCount,
            'guest_count'   => $guestCount,
            'meals'         => $meals,
            'menu'          => $menu,
            'segment'       => $segment,
            'location'      => $location,
            'personnel'     => $personnel,
            'equipment'     => $equipment,
            'saturday'      => $saturday,
            'notes'         => $notes,
            'estimated_price_per_meal' => $pricing['per_meal'],
            'estimated_monthly_total'  => $monthly,
            'ip_address'    => $ip,
            'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        AuditLogger::log('quote.submitted', [
            'reference' => $record['reference'],
            'guests'    => $guestCount,
            'meals'     => $mealCount,
            'city'      => $city,
            'segment'   => $segment,
            'per_meal'  => $pricing['per_meal'],
        ]);

        return self::json([
            'success' => true,
            'data'    => [
                'reference' => $record['reference'],
                'pricing'   => [
                    'per_person_per_meal' => $pricing['per_meal'],
                    'monthly_total'       => $monthly,
                    'monthly_total_min'   => max(0, $monthly - (int) round($monthly * 0.05)),
                    'monthly_total_max'   => $monthly + (int) round($monthly * 0.07),
                    'business_days'       => self::businessDays($saturday),
                ],
                'anonymous_suppliers' => $supplierList,
            ],
            'message' => 'Bölgenizdeki ortalama fiyat hesaplandı.',
        ]);
    }

    public function attachContact(): string
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            return self::json(['success' => false, 'error' => 'CSRF'], 419);
        }

        $rl = new RateLimiter('quick_quote_contact', limit: 5, windowSeconds: 3600);
        $ip = InflationCalculatorController::clientIp();
        if (!$rl->allow($ip)) {
            header('Retry-After: ' . $rl->retryAfter($ip));
            return self::json(['success' => false, 'error' => 'RATE_LIMITED'], 429);
        }

        $reference   = trim((string) ($_POST['reference'] ?? ''));
        $email       = trim((string) ($_POST['contact_email'] ?? ''));
        $phone       = trim((string) ($_POST['contact_phone'] ?? ''));
        $contactName = trim((string) ($_POST['contact_name'] ?? ''));
        $companyName = trim((string) ($_POST['company_name'] ?? ''));

        $errors = [];
        if (!preg_match('/^YHC-\d{4}-[A-F0-9]{4}$/', $reference)) {
            $errors['reference'] = ['Geçerli bir referans no gerekli.'];
        }
        if ($email === '' && $phone === '') {
            $errors['contact'] = ['E-posta veya telefon girin.'];
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'] = ['Geçerli e-posta giriniz.'];
        }
        if ($phone !== '' && !preg_match('/^[\d\s+()-]{7,25}$/', $phone)) {
            $errors['contact_phone'] = ['Geçerli telefon giriniz.'];
        }
        if (empty($_POST['kvkk'])) {
            $errors['kvkk'] = ['KVKK onayı zorunludur.'];
        }

        if ($errors) {
            return self::json([
                'success' => false, 'error' => 'VALIDATION_FAILED', 'errors' => $errors,
                'message' => 'Lütfen formu kontrol edin.',
            ], 422);
        }

        $record = (new QuickQuoteRepository())->attachContact($reference, [
            'contact_name'  => $contactName,
            'company_name'  => $companyName,
            'contact_email' => $email,
            'contact_phone' => $phone,
            'kvkk'          => true,
        ]);

        if (!$record) {
            return self::json([
                'success' => false, 'error' => 'NOT_FOUND',
                'message' => 'Talep bulunamadı. Önce hızlı teklif almalısınız.',
            ], 404);
        }

        AuditLogger::log('quote.contact_attached', [
            'reference' => $reference,
            'email'     => $email !== '',
            'phone'     => $phone !== '',
        ]);

        return self::json([
            'success' => true,
            'data'    => [
                'reference' => $reference,
                'message'   => 'Detaylı teklifler 24 saat içinde size gönderilecek.',
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────
    //  HESAPLAMA YARDIMCILARI (Faz 2'de gerçek maliyet matrisi)
    // ────────────────────────────────────────────────────────

    private static function deriveMeals(int $mealCount, int $guestCount): array
    {
        return match ($mealCount) {
            1 => ['ogle' => $guestCount, 'aksam' => 0, 'kumanya' => 0],
            2 => ['ogle' => $guestCount, 'aksam' => $guestCount, 'kumanya' => 0],
            3 => ['ogle' => $guestCount, 'aksam' => $guestCount, 'kumanya' => $guestCount],
            default => ['ogle' => $guestCount, 'aksam' => 0, 'kumanya' => 0],
        };
    }

    private static function estimatePricing(string $segment, array $menu, array $personnel, array $equipment, string $saturday): array
    {
        $base = match ($segment) {
            'ekonomik' => 110,
            'premium'  => 195,
            default    => 145,
        };

        $menuAddon = 0;
        if (!empty($menu['salad_bar_count'])) {
            $menuAddon += max(0, $menu['salad_bar_count'] - 2) * 4;
        }
        if (!empty($menu['dessert_rotation'])) $menuAddon += 8;
        if (($menu['drinks'] ?? 'rotation') === 'both') $menuAddon += 5;

        $personelAddon = !empty($personnel['enabled']) ? 25 : 0;
        $ekipmanAddon  = trim((string) ($equipment['requested'] ?? '')) !== '' ? 18 : 0;

        $satAddon = match ($saturday) {
            'partial' => 5,
            'yes'     => 10,
            default   => 0,
        };

        return ['per_meal' => $base + $menuAddon + $personelAddon + $ekipmanAddon + $satAddon];
    }

    private static function businessDays(string $saturday): int
    {
        return match ($saturday) { 'partial' => 24, 'yes' => 26, default => 22 };
    }

    private static function monthlyTotal(int $perMeal, int $guestCount, int $mealCount, string $saturday): int
    {
        return $perMeal * $guestCount * $mealCount * self::businessDays($saturday);
    }

    /**
     * Admin'in girdiği fiyat ayarlarından gerçek yemekçi listesi türetir.
     * Her yemekçi kendi pricing'iyle wizard sonucuna göre fiyat hesaplar.
     *
     * @param array<int,array<string,mixed>> $suppliers
     * @return array<int,array<string,mixed>>
     */
    private static function buildAnonymousFromReal(
        array $suppliers, string $segment, array $menu, array $personnel, array $equipment, string $saturday
    ): array {
        $out = [];
        foreach ($suppliers as $sup) {
            $pricing = $sup['pricing'] ?? \App\Repositories\SupplierApplicationRepository::defaultPricing();

            $base = match ($segment) {
                'ekonomik' => (int) ($pricing['ekonomik_per_meal'] ?? 110),
                'premium'  => (int) ($pricing['premium_per_meal']  ?? 195),
                default    => (int) ($pricing['genel_per_meal']    ?? 145),
            };

            $addon = 0;
            if (!empty($menu['salad_bar_count'])) {
                $addon += max(0, $menu['salad_bar_count'] - 2) * (int) ($pricing['salad_per_extra'] ?? 4);
            }
            if (!empty($menu['dessert_rotation'])) $addon += (int) ($pricing['dessert_addon'] ?? 8);
            if (($menu['drinks'] ?? 'rotation') === 'both') $addon += (int) ($pricing['drinks_both_addon'] ?? 5);
            if (!empty($personnel['enabled'])) $addon += (int) ($pricing['personnel_addon'] ?? 25);
            if (trim((string) ($equipment['requested'] ?? '')) !== '') $addon += (int) ($pricing['equipment_addon'] ?? 18);
            $addon += match ($saturday) {
                'partial' => (int) ($pricing['saturday_partial'] ?? 5),
                'yes'     => (int) ($pricing['saturday_yes']     ?? 10),
                default   => 0,
            };

            $out[] = [
                'code'           => $sup['anonymous_code'] ?? 'Yemekçi ?',
                'city'           => $sup['city'] ?? '',
                'district'       => $sup['district'] ?? '',
                'rating'         => (float) ($sup['rating'] ?? 4.5),
                'years'          => (int) ($sup['years_in_business'] ?? 10),
                'capacity'       => (int) ($sup['daily_capacity'] ?? 1000),
                'price_per_meal' => $base + $addon,
                'certifications' => array_slice((array) ($sup['certifications'] ?? []), 0, 3),
            ];
        }
        // Fiyata göre artan sıraya
        usort($out, static fn($a, $b) => $a['price_per_meal'] <=> $b['price_per_meal']);
        return $out;
    }

    private static function anonymousSuppliers(int $basePrice, string $city): array
    {
        $codes     = ['A', 'B', 'C', 'D', 'E'];
        $districts = ['Şişli', 'Kadıköy', 'Beşiktaş', 'Bakırköy', 'Maltepe'];
        $out = [];
        foreach ($codes as $i => $code) {
            $offsetPct = [-8, -3, 0, 4, 9][$i] ?? 0;
            $price = (int) round($basePrice * (1 + $offsetPct / 100));
            $out[] = [
                'code'           => 'Yemekçi ' . $code,
                'city'           => $city,
                'district'       => $districts[$i] ?? '',
                'rating'         => round(4.4 + ($i * 0.1), 1),
                'years'          => [12, 18, 25, 8, 15][$i] ?? 10,
                'capacity'       => [2000, 5000, 8000, 1500, 3500][$i] ?? 2000,
                'price_per_meal' => $price,
                'certifications' => $i === 2 ? ['ISO 22000', 'TSE Helal', 'Vegan'] : ($i === 1 ? ['ISO 22000', 'HACCP'] : ['HACCP']),
            ];
        }
        return $out;
    }

    private static function json(array $data, int $status = 200): string
    {
        http_response_code($status);
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}';
    }
}

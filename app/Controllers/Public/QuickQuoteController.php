<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Repositories\QuickQuoteRepository;
use App\Services\AuditLogger;
use App\Services\RateLimiter;

/**
 * Hızlı teklif akışı (PRD §3 — Faz 3 öne çekme):
 * 3 soru, 60 saniye, anonim 3 yemekçi listesi (ilk MVP'de talep alınır + dummy listesi).
 *
 * Kullanıcının veriyi POST etmesi: 3 adımlı Alpine.js wizard tek formda.
 */
final class QuickQuoteController
{
    private const MEAL_TYPES = ['ogle' => 'Öğle', 'aksam' => 'Akşam', 'kumanya' => 'Kumanya', 'cocktail' => 'Cocktail'];

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

        // Adım 1: Kişi sayısı
        $guestCount = (int) ($_POST['guest_count'] ?? 0);
        if ($guestCount < 5 || $guestCount > 5000) {
            $errors['guest_count'] = ['Kişi sayısı 5 ile 5000 arasında olmalı.'];
        }

        // Adım 2: Öğün
        $mealType = (string) ($_POST['meal_type'] ?? '');
        if (!isset(self::MEAL_TYPES[$mealType])) {
            $errors['meal_type'] = ['Lütfen bir öğün tipi seçin.'];
        }

        // Adım 3: Tarih + lokasyon
        $eventDate = trim((string) ($_POST['event_date'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
            $errors['event_date'] = ['Tarih YYYY-AA-GG biçiminde olmalı.'];
        } elseif (strtotime($eventDate) < strtotime('today')) {
            $errors['event_date'] = ['Tarih bugün veya sonrası olmalı.'];
        }

        $city = trim((string) ($_POST['city'] ?? ''));
        if (mb_strlen($city) < 2 || mb_strlen($city) > 80) {
            $errors['city'] = ['Şehir 2-80 karakter olmalı.'];
        }

        // İletişim (en az birisi zorunlu)
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

        // KVKK
        if (empty($_POST['kvkk'])) {
            $errors['kvkk'] = ['Aydınlatma metnini ve iletişim onayını kabul etmelisiniz.'];
        }

        if ($errors) {
            return self::json([
                'success' => false,
                'error'   => 'VALIDATION_FAILED',
                'errors'  => $errors,
                'message' => 'Lütfen formu kontrol edin.',
            ], 422);
        }

        AuditLogger::log('quote.submitted', [
            'guests' => $guestCount, 'meal' => $mealType, 'date' => $eventDate, 'city' => $city,
        ]);
        $record = (new QuickQuoteRepository())->create([
            'guest_count'   => $guestCount,
            'meal_type'     => $mealType,
            'event_date'    => $eventDate,
            'city'          => $city,
            'district'      => trim((string) ($_POST['district'] ?? '')),
            'contact_name'  => trim((string) ($_POST['contact_name'] ?? '')),
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'notes'         => trim((string) ($_POST['notes'] ?? '')),
            'ip_address'    => $ip,
            'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        return self::json([
            'success' => true,
            'data'    => [
                'reference'   => $record['reference'],
                'message'     => 'Talebiniz alındı. Onaylı yemekçilerden teklifler 24 saat içinde geliyor olacak.',
                'next_steps'  => [
                    '24 saat içinde 3 yemekçinin tekliflerini e-posta/SMS ile alacaksınız.',
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

<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Auth\SimpleAuth;
use App\Repositories\InflationCalculationRepository;
use App\Services\AuditLogger;
use App\Services\InflationCalculator;
use App\Services\RateLimiter;
use DateTimeImmutable;

/**
 * Müşteri tarafı enflasyon hesaplayıcı (Faz 0.5 — PRD Bölüm 25.6.1).
 *
 * Üç panelde de aynı motor kullanılır; bu controller public sayfayı sunar.
 * `/yemekci/araclar/enflasyon` ve `/yonetim/araclar/enflasyon` rotaları
 * Faz 1.0b sonrası eklenecek (auth gerektirir).
 */
final class InflationCalculatorController
{
    public function show(): string
    {
        $content = \view('public.inflation-calculator', [
            'sources' => InflationCalculator::sources(),
        ]);

        return \layout('app', $content, [
            'title'       => 'Enflasyon Hesaplayıcı — Yemekhaneci',
            'description' => 'Geçmiş yemek ve catering fiyatlarınızın bugünkü karşılığını TÜİK ve ENAG endeksleriyle hesaplayın.',
            'authed'      => SimpleAuth::check(),
            'user'        => SimpleAuth::user(),
        ]);
    }

    /**
     * JSON API — formdan gelen değerlere göre hesap döndürür.
     */
    public function calculate(): string
    {
        header('Content-Type: application/json; charset=utf-8');

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!\csrf_check($token)) {
            return self::json([
                'success' => false,
                'error'   => 'CSRF',
                'message' => 'Oturum doğrulaması başarısız.',
            ], 419);
        }

        // Rate limit (PRD §25.8): public endpoint IP başı 30/dk
        $rl = new RateLimiter('inflation_calc', limit: 30, windowSeconds: 60);
        $ip = self::clientIp();
        if (!$rl->allow($ip)) {
            header('Retry-After: ' . $rl->retryAfter($ip));
            return self::json([
                'success'     => false,
                'error'       => 'RATE_LIMITED',
                'message'     => 'Çok sık istek gönderdiniz. Lütfen biraz sonra tekrar deneyin.',
                'retry_after' => $rl->retryAfter($ip),
            ], 429);
        }

        $sourceCode = trim((string) ($_POST['source'] ?? ''));
        $startDateRaw = trim((string) ($_POST['start_date'] ?? ''));
        $endDateRaw = trim((string) ($_POST['end_date'] ?? ''));
        $startPriceRaw = trim((string) ($_POST['start_price'] ?? ''));

        $errors = [];
        if ($sourceCode === '') {
            $errors['source'] = ['Kaynak seçimi zorunludur.'];
        }

        $startDate = self::parseMonth($startDateRaw);
        if (!$startDate) {
            $errors['start_date'] = ['Geçerli bir başlangıç ayı seçin (YYYY-MM).'];
        }

        $endDate = self::parseMonth($endDateRaw);
        if (!$endDate) {
            $errors['end_date'] = ['Geçerli bir hedef ay seçin (YYYY-MM).'];
        }

        $startPrice = self::parsePrice($startPriceRaw);
        if ($startPrice === null || $startPrice <= 0) {
            $errors['start_price'] = ['Başlangıç fiyatı sıfırdan büyük bir sayı olmalı.'];
        }

        if ($errors) {
            return self::json([
                'success' => false,
                'error'   => 'VALIDATION_FAILED',
                'message' => 'Form alanlarını kontrol edin.',
                'errors'  => $errors,
            ], 422);
        }

        try {
            $result = InflationCalculator::calculate($sourceCode, $startDate, (float) $startPrice, $endDate);
        } catch (\Throwable $e) {
            return self::json([
                'success' => false,
                'error'   => 'CALC_FAILED',
                'message' => $e->getMessage(),
            ], 400);
        }

        // Anonim hesaplama kaydı (PRD §25.3.3) — e-posta yok, KVKK gerekmiyor.
        $panelOrigin = self::sanitizePanelOrigin((string) ($_POST['panel_origin'] ?? 'public'));
        try {
            (new InflationCalculationRepository())->create([
                'source_code'   => $sourceCode,
                'start_date'    => $result['start_period'] . '-01',
                'start_price'   => (float) $startPrice,
                'end_date'      => $result['end_period'] . '-01',
                'end_price'     => (float) $result['end_price'],
                'change_pct'    => (float) $result['change_pct'],
                'email'         => null,
                'kvkk_accepted' => false,
                'ip_address'    => self::clientIp(),
                'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'panel_origin'  => $panelOrigin,
            ]);
        } catch (\Throwable) {
            // Log akışı sessiz — analytics hatası kullanıcı işlemini bozmasın.
        }

        return self::json([
            'success' => true,
            'data'    => $result,
            'message' => 'Hesaplama başarıyla tamamlandı.',
        ]);
    }

    /**
     * Lead capture (PRD §25.6.1, §25.7) — KVKK onaylı e-posta + son hesap sonucu.
     */
    public function submitLead(): string
    {
        header('Content-Type: application/json; charset=utf-8');

        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            return self::json([
                'success' => false,
                'error'   => 'CSRF',
                'message' => 'Oturum doğrulaması başarısız.',
            ], 419);
        }

        $rl = new RateLimiter('inflation_lead', limit: 5, windowSeconds: 3600);
        $ip = self::clientIp();
        if (!$rl->allow($ip)) {
            header('Retry-After: ' . $rl->retryAfter($ip));
            return self::json([
                'success'     => false,
                'error'       => 'RATE_LIMITED',
                'message'     => 'Saatlik gönderim limitiniz doldu. Lütfen 1 saat sonra tekrar deneyin.',
                'retry_after' => $rl->retryAfter($ip),
            ], 429);
        }

        $email     = trim((string) ($_POST['email'] ?? ''));
        $kvkkOk    = !empty($_POST['kvkk']) && $_POST['kvkk'] !== '0';
        $sourceCode= trim((string) ($_POST['source'] ?? ''));
        $startDateRaw = trim((string) ($_POST['start_date'] ?? ''));
        $endDateRaw   = trim((string) ($_POST['end_date'] ?? ''));
        $startPriceRaw= trim((string) ($_POST['start_price'] ?? ''));
        $panelOrigin = self::sanitizePanelOrigin((string) ($_POST['panel_origin'] ?? 'public'));

        $errors = [];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = ['Geçerli bir e-posta giriniz.'];
        }
        if (!$kvkkOk) {
            $errors['kvkk'] = ['Aydınlatma metnini ve e-posta gönderim onayını kabul etmelisiniz.'];
        }

        $startDate = self::parseMonth($startDateRaw);
        $endDate = self::parseMonth($endDateRaw);
        $startPrice = self::parsePrice($startPriceRaw);
        if (!$sourceCode || !$startDate || !$endDate || $startPrice === null || $startPrice <= 0) {
            $errors['form'] = ['Hesaplama bilgileri eksik veya geçersiz.'];
        }

        if ($errors) {
            return self::json([
                'success' => false,
                'error'   => 'VALIDATION_FAILED',
                'errors'  => $errors,
                'message' => 'Lütfen e-posta ve onay alanlarını kontrol edin.',
            ], 422);
        }

        try {
            $result = InflationCalculator::calculate($sourceCode, $startDate, (float) $startPrice, $endDate);
        } catch (\Throwable $e) {
            return self::json([
                'success' => false,
                'error'   => 'CALC_FAILED',
                'message' => $e->getMessage(),
            ], 400);
        }

        $record = (new InflationCalculationRepository())->create([
            'source_code'   => $sourceCode,
            'start_date'    => $result['start_period'] . '-01',
            'start_price'   => (float) $startPrice,
            'end_date'      => $result['end_period'] . '-01',
            'end_price'     => (float) $result['end_price'],
            'change_pct'    => (float) $result['change_pct'],
            'email'         => $email,
            'kvkk_accepted' => true,
            'ip_address'    => $ip,
            'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'panel_origin'  => $panelOrigin,
        ]);

        AuditLogger::log('lead.captured', ['email' => $email, 'source' => $sourceCode, 'panel' => $panelOrigin]);

        return self::json([
            'success' => true,
            'data'    => [
                'id' => $record['id'],
                'message' => 'Hesap sonucunuz kayıt altına alındı. SMTP entegrasyonu Faz 4\'te aktif olunca '
                    . e($email) . ' adresine gönderim yapılacak.',
            ],
            'message' => 'Talebiniz alındı.',
        ]);
    }

    private static function sanitizePanelOrigin(string $raw): string
    {
        return in_array($raw, ['public', 'supplier', 'admin'], true) ? $raw : 'public';
    }

    private static function parseMonth(string $raw): ?DateTimeImmutable
    {
        if ($raw === '') return null;
        // Hem YYYY-MM hem YYYY-MM-DD kabul et.
        if (preg_match('/^(\d{4})-(\d{2})$/', $raw, $m)) {
            return DateTimeImmutable::createFromFormat('!Y-m', $raw) ?: null;
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw)) {
            return DateTimeImmutable::createFromFormat('!Y-m-d', $raw) ?: null;
        }
        return null;
    }

    private static function parsePrice(string $raw): ?float
    {
        if ($raw === '') return null;
        // "12.345,67" veya "12345.67" veya "12345" kabul et.
        $normalized = str_replace(['.', ','], ['', '.'], $raw);
        if (substr_count($raw, ',') === 1 && substr_count($raw, '.') > 0) {
            // TR formatı: 12.345,67
            $normalized = str_replace('.', '', $raw);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (substr_count($raw, ',') === 1 && substr_count($raw, '.') === 0) {
            $normalized = str_replace(',', '.', $raw);
        } else {
            $normalized = $raw;
        }
        if (!is_numeric($normalized)) return null;
        return (float) $normalized;
    }

    private static function json(array $data, int $status = 200): string
    {
        http_response_code($status);
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) ?: '{}';
    }

    public static function clientIp(): string
    {
        // Reverse proxy varsa X-Forwarded-For ilk değeri (Traefik/Cloudflare)
        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($forwarded !== '') {
            $first = trim(explode(',', $forwarded)[0]);
            if ($first !== '' && filter_var($first, FILTER_VALIDATE_IP)) {
                return $first;
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

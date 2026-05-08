<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Auth\SimpleAuth;
use App\Services\InflationCalculator;
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

        return self::json([
            'success' => true,
            'data'    => $result,
            'message' => 'Hesaplama başarıyla tamamlandı.',
        ]);
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
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\InflationSourceRepository;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Enflasyon hesaplama motoru — PRD Bölüm 25.4.
 *
 * Faz 0.5 demo: veriler in-memory mock setten okunur (gerçekçi ama sentetik).
 * Faz 0.5.6 sonu: `inflation_indices` tablosundan PDO ile okunacak,
 * Faz 0.5.7 sonu: TCMB EVDS API ile aylık otomatik tetik.
 */
final class InflationCalculator
{
    /**
     * Mock aylık endeks değerleri.
     *
     * @return array<string, array<string, array{value:float, monthly_pct:?float, yearly_pct:?float}>>
     *         Yapı: [source_code][YYYY-MM] = ['value'=>..., 'monthly_pct'=>..., 'yearly_pct'=>...]
     */
    public static function mockIndices(): array
    {
        $repo = new InflationSourceRepository();
        $synthetic = self::generateMockSeries();
        $custom    = $repo->customMonthlySeries();
        $official  = $repo->officialMonthlySeries();

        // Resmî kaynak için DB değerleri varsa onlar; yoksa sentetik baseline.
        $merged = $synthetic;
        foreach ($official as $code => $series) {
            if (!empty($series)) {
                $merged[$code] = $series;
            }
        }
        // Custom kaynaklar (rezerve değil; resmî kodlarla çakışamaz).
        foreach ($custom as $code => $series) {
            $merged[$code] = $series;
        }
        return $merged;
    }

    /**
     * Aktif kaynaklar (UI için) — resmî + admin'in oluşturduğu özel formüller.
     *
     * @return array<int, array{code:string, name:string, color:string, base_period:string, is_official:bool}>
     */
    public static function sources(): array
    {
        $repo = new InflationSourceRepository();
        $out = [];
        foreach ($repo->all() as $src) {
            if (!($src['is_active'] ?? true)) continue;
            $out[] = [
                'code'        => $src['code'],
                'name'        => $src['name'],
                'color'       => $src['color_hex'] ?? '#6B1F2A',
                'base_period' => $src['base_period'] ?? '',
                'is_official' => (bool) ($src['is_official'] ?? false),
            ];
        }
        return $out;
    }

    /**
     * Hesaplama: end_price = start_price × (end_index / start_index).
     *
     * @return array{
     *     source_code: string,
     *     start_period: string, start_index: float, start_price: float,
     *     end_period: string, end_index: float, end_price: float,
     *     change_pct: float, monthly_avg_pct: float,
     *     monthly_series: array<int, array{period:string, index:float}>,
     *     warning: ?string
     * }
     */
    public static function calculate(
        string $sourceCode,
        DateTimeImmutable $startDate,
        float $startPrice,
        DateTimeImmutable $endDate
    ): array {
        if ($startPrice <= 0) {
            throw new InvalidArgumentException('Başlangıç fiyatı sıfırdan büyük olmalı.');
        }
        if ($endDate < $startDate) {
            throw new InvalidArgumentException('Hedef tarih başlangıç tarihinden önce olamaz.');
        }

        $indices = self::mockIndices();
        if (!isset($indices[$sourceCode])) {
            throw new InvalidArgumentException("Geçersiz kaynak: {$sourceCode}");
        }

        $series = $indices[$sourceCode];
        $startKey = $startDate->format('Y-m');
        $endKey = $endDate->format('Y-m');

        $warning = null;

        // Hedef ay henüz açıklanmamışsa son mevcut aya geri çekil.
        if (!isset($series[$endKey])) {
            $endKey = self::lastAvailableKey($series, $endKey);
            $warning = 'Hedef ay verisi henüz açıklanmadığı için en son açıklanan ay (' . $endKey . ') kullanıldı.';
        }
        if (!isset($series[$startKey])) {
            // Başlangıç verisi yoksa (örn. çok eski) en yakın aya iter.
            $startKey = self::firstAvailableKey($series, $startKey);
            $warning = ($warning ? $warning . ' ' : '') . 'Başlangıç ayı veri setinde yoktu, en yakın ay (' . $startKey . ') kullanıldı.';
        }

        $startIndex = $series[$startKey]['value'];
        $endIndex = $series[$endKey]['value'];

        $endPrice = round($startPrice * ($endIndex / $startIndex), 2);
        $changePct = round((($endIndex - $startIndex) / $startIndex) * 100, 4);

        // Aylık ortalama bileşik artış oranı.
        $monthsBetween = self::monthsBetween($startKey, $endKey);
        $monthlyAvgPct = $monthsBetween > 0
            ? round((pow($endIndex / $startIndex, 1 / $monthsBetween) - 1) * 100, 4)
            : 0.0;

        // Grafik için aylık seri (start..end).
        $monthlySeries = [];
        $cursor = $startKey;
        while (true) {
            if (isset($series[$cursor])) {
                $monthlySeries[] = ['period' => $cursor, 'index' => $series[$cursor]['value']];
            }
            if ($cursor === $endKey) {
                break;
            }
            $cursor = self::nextMonth($cursor);
            // Güvenlik: 600 ay (~50 yıl) limiti.
            if (count($monthlySeries) > 600) break;
        }

        return [
            'source_code'     => $sourceCode,
            'start_period'    => $startKey,
            'start_index'     => $startIndex,
            'start_price'     => $startPrice,
            'end_period'      => $endKey,
            'end_index'       => $endIndex,
            'end_price'       => $endPrice,
            'change_pct'      => $changePct,
            'monthly_avg_pct' => $monthlyAvgPct,
            'monthly_series'  => $monthlySeries,
            'warning'         => $warning,
        ];
    }

    private static function lastAvailableKey(array $series, string $target): string
    {
        $keys = array_keys($series);
        sort($keys);
        $lastBefore = null;
        foreach ($keys as $k) {
            if ($k <= $target) $lastBefore = $k;
        }
        return $lastBefore ?? end($keys);
    }

    private static function firstAvailableKey(array $series, string $target): string
    {
        $keys = array_keys($series);
        sort($keys);
        foreach ($keys as $k) {
            if ($k >= $target) return $k;
        }
        return end($keys);
    }

    private static function monthsBetween(string $a, string $b): int
    {
        [$ay, $am] = array_map('intval', explode('-', $a));
        [$by, $bm] = array_map('intval', explode('-', $b));
        return ($by - $ay) * 12 + ($bm - $am);
    }

    private static function nextMonth(string $key): string
    {
        [$y, $m] = array_map('intval', explode('-', $key));
        $m++;
        if ($m > 12) { $m = 1; $y++; }
        return sprintf('%04d-%02d', $y, $m);
    }

    /**
     * Sentetik aylık seri üretir — Ocak 2020'den 2026 Mayıs'a kadar.
     * Her kaynak farklı bileşik aylık artış oranı kullanır (Türkiye'nin son 5 yıl gerçekleriyle uyumlu).
     */
    private static function generateMockSeries(): array
    {
        // (kaynak_kod => [başlangıç_endeks, aylık_ortalama_artış_oranı_yüzde])
        $config = [
            'tuik_tufe'      => ['start' => 580.0,  'rate' => 2.65],
            'tuik_tufe_gida' => ['start' => 670.0,  'rate' => 3.05],
            'tuik_yiufe'     => ['start' => 510.0,  'rate' => 2.35],
            'enag_tufe'      => ['start' => 130.0,  'rate' => 4.10],
        ];

        $series = [];
        foreach ($config as $code => $cfg) {
            $value = $cfg['start'];
            $monthly = [];
            $previousValue = null;
            $year = 2020;
            $month = 1;
            $endYear = 2026;
            $endMonth = 5;

            while ($year < $endYear || ($year === $endYear && $month <= $endMonth)) {
                // Hafif sezonsal dalgalanma (gıda için daha belirgin).
                $seasonalAdj = $code === 'tuik_tufe_gida'
                    ? (sin(($month - 1) / 12 * 2 * M_PI) * 0.4)
                    : (sin(($month - 1) / 12 * 2 * M_PI) * 0.15);
                $monthlyRate = $cfg['rate'] + $seasonalAdj;

                if ($previousValue === null) {
                    // İlk ay: başlangıç değeri olduğu gibi.
                    $valueRounded = round($value, 4);
                } else {
                    $value = $previousValue * (1 + $monthlyRate / 100);
                    $valueRounded = round($value, 4);
                }

                $key = sprintf('%04d-%02d', $year, $month);
                $monthly[$key] = [
                    'value'        => $valueRounded,
                    'monthly_pct'  => $previousValue === null ? null : round($monthlyRate, 4),
                    'yearly_pct'   => null, // (12 ay önceki referansla doldurulabilir; demo için boş)
                ];

                $previousValue = $valueRounded;
                $month++;
                if ($month > 12) { $month = 1; $year++; }
            }

            // Yıllık değişim oranını ikinci geçişte doldur.
            foreach ($monthly as $k => $row) {
                [$y, $m] = array_map('intval', explode('-', $k));
                $prevYearKey = sprintf('%04d-%02d', $y - 1, $m);
                if (isset($monthly[$prevYearKey])) {
                    $monthly[$k]['yearly_pct'] = round((($row['value'] / $monthly[$prevYearKey]['value']) - 1) * 100, 4);
                }
            }

            $series[$code] = $monthly;
        }

        return $series;
    }
}

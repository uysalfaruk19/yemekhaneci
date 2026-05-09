<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\InflationCalculator;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class InflationCalculatorTest extends TestCase
{
    public function test_ayni_donemde_fiyat_degismez(): void
    {
        $r = InflationCalculator::calculate(
            'tuik_tufe',
            new DateTimeImmutable('2025-01-01'),
            1000.0,
            new DateTimeImmutable('2025-01-01')
        );
        $this->assertEqualsWithDelta(1000.0, $r['end_price'], 0.01);
        $this->assertEqualsWithDelta(0.0, $r['change_pct'], 0.01);
    }

    public function test_pozitif_fiyat_zorunlu(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InflationCalculator::calculate(
            'tuik_tufe',
            new DateTimeImmutable('2024-01-01'),
            0.0,
            new DateTimeImmutable('2025-01-01')
        );
    }

    public function test_hedef_tarih_baslangictan_once_olamaz(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InflationCalculator::calculate(
            'tuik_tufe',
            new DateTimeImmutable('2025-06-01'),
            1000.0,
            new DateTimeImmutable('2025-01-01')
        );
    }

    public function test_gecersiz_kaynak_kodu_hata(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InflationCalculator::calculate(
            'olmayan_kod',
            new DateTimeImmutable('2024-01-01'),
            1000.0,
            new DateTimeImmutable('2025-01-01')
        );
    }

    public function test_formul_dogrulugu(): void
    {
        $r = InflationCalculator::calculate(
            'tuik_tufe_gida',
            new DateTimeImmutable('2024-01-01'),
            5000.0,
            new DateTimeImmutable('2025-12-01')
        );
        $expected = round(5000.0 * ($r['end_index'] / $r['start_index']), 2);
        $this->assertEqualsWithDelta($expected, $r['end_price'], 0.01);
    }

    public function test_aylik_ortalama_bilesik(): void
    {
        $r = InflationCalculator::calculate(
            'tuik_tufe',
            new DateTimeImmutable('2024-01-01'),
            1000.0,
            new DateTimeImmutable('2025-01-01')
        );
        $expectedMonthly = (pow($r['end_index'] / $r['start_index'], 1 / 12) - 1) * 100;
        $this->assertEqualsWithDelta($expectedMonthly, $r['monthly_avg_pct'], 0.001);
    }

    public function test_monthly_series_aralik_kapsar(): void
    {
        $r = InflationCalculator::calculate(
            'tuik_yiufe',
            new DateTimeImmutable('2024-01-01'),
            1000.0,
            new DateTimeImmutable('2024-06-01')
        );
        $this->assertCount(6, $r['monthly_series']);
        $this->assertSame('2024-01', $r['monthly_series'][0]['period']);
        $this->assertSame('2024-06', $r['monthly_series'][5]['period']);
    }

    public function test_change_pct_yon_pozitif_enflasyonda(): void
    {
        $r = InflationCalculator::calculate(
            'tuik_tufe_gida',
            new DateTimeImmutable('2023-01-01'),
            1000.0,
            new DateTimeImmutable('2026-01-01')
        );
        $this->assertGreaterThan(0, $r['change_pct']);
    }

    public function test_sources_resmi_4_kaynak_icerir(): void
    {
        $sources = InflationCalculator::sources();
        $codes = array_column($sources, 'code');
        foreach (['tuik_tufe', 'tuik_tufe_gida', 'tuik_yiufe', 'enag_tufe'] as $expected) {
            $this->assertContains($expected, $codes);
        }
    }
}

<?php

declare(strict_types=1);

use App\Services\InflationCalculator;
use Tests\TestRunner;

TestRunner::group('InflationCalculator — formül ve kenar durumlar', function () {

    TestRunner::run('Aynı endeksle aynı tarih → fiyat değişmemeli', function () {
        $r = InflationCalculator::calculate(
            'tuik_tufe',
            new DateTimeImmutable('2025-01-01'),
            1000.0,
            new DateTimeImmutable('2025-01-01')
        );
        TestRunner::assertEqualsWithDelta(1000.0, $r['end_price'], 0.01, 'Aynı dönemde fiyat aynı olmalı');
        TestRunner::assertEqualsWithDelta(0.0, $r['change_pct'], 0.01);
    });

    TestRunner::run('Pozitif fiyat zorunlu', function () {
        TestRunner::assertThrows(InvalidArgumentException::class, function () {
            InflationCalculator::calculate(
                'tuik_tufe',
                new DateTimeImmutable('2024-01-01'),
                0.0,
                new DateTimeImmutable('2025-01-01')
            );
        });
    });

    TestRunner::run('Hedef tarih başlangıçtan önce olamaz', function () {
        TestRunner::assertThrows(InvalidArgumentException::class, function () {
            InflationCalculator::calculate(
                'tuik_tufe',
                new DateTimeImmutable('2025-06-01'),
                1000.0,
                new DateTimeImmutable('2025-01-01')
            );
        });
    });

    TestRunner::run('Geçersiz kaynak kodu hata fırlatır', function () {
        TestRunner::assertThrows(InvalidArgumentException::class, function () {
            InflationCalculator::calculate(
                'olmayan_kod',
                new DateTimeImmutable('2024-01-01'),
                1000.0,
                new DateTimeImmutable('2025-01-01')
            );
        });
    });

    TestRunner::run('Formül doğruluğu: end_price = start_price × (end_idx / start_idx)', function () {
        $r = InflationCalculator::calculate(
            'tuik_tufe_gida',
            new DateTimeImmutable('2024-01-01'),
            5000.0,
            new DateTimeImmutable('2025-12-01')
        );
        $expected = round(5000.0 * ($r['end_index'] / $r['start_index']), 2);
        TestRunner::assertEqualsWithDelta($expected, $r['end_price'], 0.01,
            "5000 × ({$r['end_index']} / {$r['start_index']}) ≈ {$expected}");
    });

    TestRunner::run('Aylık ortalama bileşik artış geometric mean ile uyumlu', function () {
        $r = InflationCalculator::calculate(
            'tuik_tufe',
            new DateTimeImmutable('2024-01-01'),
            1000.0,
            new DateTimeImmutable('2025-01-01')
        );
        // 12 ay → (end/start)^(1/12) - 1
        $expectedMonthly = (pow($r['end_index'] / $r['start_index'], 1 / 12) - 1) * 100;
        TestRunner::assertEqualsWithDelta($expectedMonthly, $r['monthly_avg_pct'], 0.001);
    });

    TestRunner::run('monthly_series start..end aralığını kapsar', function () {
        $r = InflationCalculator::calculate(
            'tuik_yiufe',
            new DateTimeImmutable('2024-01-01'),
            1000.0,
            new DateTimeImmutable('2024-06-01')
        );
        TestRunner::assertSame(6, count($r['monthly_series']), 'Ocak-Haziran 2024 = 6 ay');
        TestRunner::assertSame('2024-01', $r['monthly_series'][0]['period']);
        TestRunner::assertSame('2024-06', $r['monthly_series'][5]['period']);
    });

    TestRunner::run('change_pct yön doğru (pozitif enflasyonda > 0)', function () {
        $r = InflationCalculator::calculate(
            'tuik_tufe_gida',
            new DateTimeImmutable('2023-01-01'),
            1000.0,
            new DateTimeImmutable('2026-01-01')
        );
        TestRunner::assertTrue($r['change_pct'] > 0, "Beklenen pozitif change_pct, gerçek {$r['change_pct']}");
    });

    TestRunner::run('sources() resmî 4 kaynak içerir', function () {
        $sources = InflationCalculator::sources();
        $codes = array_column($sources, 'code');
        foreach (['tuik_tufe', 'tuik_tufe_gida', 'tuik_yiufe', 'enag_tufe'] as $expected) {
            TestRunner::assertTrue(in_array($expected, $codes, true), "{$expected} eksik");
        }
    });
});

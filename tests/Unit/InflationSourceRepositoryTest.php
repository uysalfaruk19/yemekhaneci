<?php

declare(strict_types=1);

use App\Repositories\InflationSourceRepository;
use Tests\TestRunner;

TestRunner::group('InflationSourceRepository — CRUD ve aylık veri', function () {

    $tmpFile = sys_get_temp_dir() . '/yh_test_repo_' . uniqid() . '.json';

    TestRunner::run('Resmî 4 kaynak her zaman görünür', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        $codes = array_column($repo->all(), 'code');
        foreach (['tuik_tufe', 'tuik_tufe_gida', 'tuik_yiufe', 'enag_tufe'] as $expected) {
            TestRunner::assertTrue(in_array($expected, $codes, true), "{$expected} eksik");
        }
    });

    TestRunner::run('Yeni özel kaynak oluşturma + bulma', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        $rec = $repo->createCustom([
            'code' => 'uysa_test_endeksi', 'name' => 'UYSA Test', 'unit' => 'tl_kg',
            'color_hex' => '#abcdef', 'display_order' => 100,
        ], 'TESTUSER');
        TestRunner::assertSame('uysa_test_endeksi', $rec['code']);
        TestRunner::assertSame('custom_admin', $rec['source_type']);

        $found = $repo->findCustom('uysa_test_endeksi');
        TestRunner::assertTrue($found !== null);
        TestRunner::assertSame('UYSA Test', $found['name']);
    });

    TestRunner::run('Rezerve önek (tuik_, enag_) reddedilir', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        TestRunner::assertThrows(RuntimeException::class, function () use ($repo) {
            $repo->createCustom(['code' => 'tuik_yeni', 'name' => 'Çakışan'], 'X');
        });
        TestRunner::assertThrows(RuntimeException::class, function () use ($repo) {
            $repo->createCustom(['code' => 'enag_yeni', 'name' => 'Çakışan'], 'X');
        });
    });

    TestRunner::run('Duplicate kod reddedilir', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        TestRunner::assertThrows(RuntimeException::class, function () use ($repo) {
            $repo->createCustom(['code' => 'uysa_test_endeksi', 'name' => 'Tekrar'], 'X');
        });
    });

    TestRunner::run('Aylık veri ekleme + okuma', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        $repo->addMonthlyValue('uysa_test_endeksi', 2025, 1, 100.0, 'ilk', 'OFU');
        $repo->addMonthlyValue('uysa_test_endeksi', 2025, 2, 105.0, null, 'OFU');
        $repo->addMonthlyValue('uysa_test_endeksi', 2025, 3, 110.5, null, 'OFU');

        $values = $repo->monthlyValues('uysa_test_endeksi');
        TestRunner::assertSame(3, count($values));
        TestRunner::assertEqualsWithDelta(105.0, (float) $values['2025-02']['value'], 0.001);
    });

    TestRunner::run('Aylık veri için ay/yıl/değer validasyonu', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        TestRunner::assertThrows(RuntimeException::class, fn() => $repo->addMonthlyValue('uysa_test_endeksi', 2002, 1, 50.0, null, 'X'));
        TestRunner::assertThrows(RuntimeException::class, fn() => $repo->addMonthlyValue('uysa_test_endeksi', 2025, 13, 50.0, null, 'X'));
        TestRunner::assertThrows(RuntimeException::class, fn() => $repo->addMonthlyValue('uysa_test_endeksi', 2025, 6, -1.0, null, 'X'));
    });

    TestRunner::run('Custom monthly series monthly_pct + yearly_pct hesaplar', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        $repo->addMonthlyValue('uysa_test_endeksi', 2026, 1, 121.0, null, 'OFU');
        $series = $repo->customMonthlySeries();
        TestRunner::assertTrue(isset($series['uysa_test_endeksi']['2026-01']));
        // 2026-01 vs 2025-01: 121/100 - 1 = 0.21 → +%21
        $row = $series['uysa_test_endeksi']['2026-01'];
        TestRunner::assertEqualsWithDelta(21.0, (float) $row['yearly_pct'], 0.01);
    });

    TestRunner::run('Aylık veri silme', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        $repo->deleteMonthlyValue('uysa_test_endeksi', '2025-02');
        $values = $repo->monthlyValues('uysa_test_endeksi');
        TestRunner::assertFalse(isset($values['2025-02']), '2025-02 silinmiş olmalı');
    });

    TestRunner::run('Custom kaynak güncelleme', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        $updated = $repo->updateCustom('uysa_test_endeksi', ['name' => 'UYSA Test (Yeni)'], 'OFU2');
        TestRunner::assertSame('UYSA Test (Yeni)', $updated['name']);
        TestRunner::assertSame('OFU2', $updated['updated_by']);
    });

    TestRunner::run('Custom kaynak silme', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        $repo->deleteCustom('uysa_test_endeksi');
        TestRunner::assertTrue($repo->findCustom('uysa_test_endeksi') === null);
    });

    TestRunner::run('Resmî kaynak setOfficialMonthlyValue + officialMonthlySeries', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        $repo->setOfficialMonthlyValue('tuik_tufe', '2025-01', 1234.5, 'evds:test');
        $repo->setOfficialMonthlyValue('tuik_tufe', '2025-02', 1267.4, 'evds:test');
        $values = $repo->officialMonthlyValues('tuik_tufe');
        TestRunner::assertSame(2, count($values));
        $series = $repo->officialMonthlySeries();
        TestRunner::assertTrue(isset($series['tuik_tufe']['2025-02']));
        TestRunner::assertEqualsWithDelta(2.6651, (float) $series['tuik_tufe']['2025-02']['monthly_pct'], 0.01);
    });

    TestRunner::run('EVDS run damgası kayıt', function () use ($tmpFile) {
        $repo = new InflationSourceRepository($tmpFile);
        $repo->recordEvdsRun('success', 'Test çalışması');
        $meta = $repo->evdsRunMeta();
        TestRunner::assertSame('success', $meta['last_status']);
        TestRunner::assertContains('Test', (string) $meta['last_message']);
        TestRunner::assertTrue($meta['runs'] >= 1);
    });

    // Cleanup
    if (file_exists($tmpFile)) unlink($tmpFile);
});

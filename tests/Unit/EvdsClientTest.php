<?php

declare(strict_types=1);

use App\Services\EvdsClient;
use Tests\TestRunner;

TestRunner::group('EvdsClient — mock mode + parse', function () {

    TestRunner::run('API key yoksa otomatik mock mode', function () {
        $c = new EvdsClient(apiKey: null);
        TestRunner::assertTrue($c->isMockMode(), 'Boş key → MOCK olmalı');
    });

    TestRunner::run('API key varsa LIVE mode', function () {
        $c = new EvdsClient(apiKey: 'TESTKEY', mockMode: false);
        TestRunner::assertFalse($c->isMockMode(), 'Key var ve mockMode=false → LIVE');
    });

    TestRunner::run('Mock seri 25 ay döndürür (24 ay önceden bugüne)', function () {
        $c = new EvdsClient(apiKey: null, mockMode: true);
        $series = $c->fetchMonthlySeries('TP.FG.J0', '2024-01', '2026-01');
        TestRunner::assertSame(25, count($series), 'Ocak 2024 - Ocak 2026 = 25 ay');
        TestRunner::assertTrue(isset($series['2024-01']));
        TestRunner::assertTrue(isset($series['2026-01']));
    });

    TestRunner::run('Mock seri zaman içinde monoton artar (enflasyon pozitif)', function () {
        $c = new EvdsClient(apiKey: null, mockMode: true);
        $series = $c->fetchMonthlySeries('TP.FG.J01', '2024-01', '2025-12');
        $values = array_values($series);
        for ($i = 1; $i < count($values); $i++) {
            TestRunner::assertTrue(
                $values[$i] > $values[$i - 1],
                "Mock veride enflasyon pozitif olmalı, ay {$i}: {$values[$i-1]} → {$values[$i]}"
            );
        }
    });

    TestRunner::run('Farklı endeks kodları farklı baz değerlerinden başlar', function () {
        $c = new EvdsClient(apiKey: null, mockMode: true);
        $tufe = $c->fetchMonthlySeries('TP.FG.J0', '2024-01', '2024-01')['2024-01'];
        $gida = $c->fetchMonthlySeries('TP.FG.J01', '2024-01', '2024-01')['2024-01'];
        $yiufe = $c->fetchMonthlySeries('TP.FE.OKTG01', '2024-01', '2024-01')['2024-01'];
        // Üç değer de birbirinden farklı olmalı
        TestRunner::assertFalse($tufe == $gida, 'TÜFE ve Gıda farklı seri olmalı');
        TestRunner::assertFalse($tufe == $yiufe, 'TÜFE ve Yİ-ÜFE farklı seri olmalı');
    });

    TestRunner::run('Geçersiz tarih biçiminde live modda hata fırlatır', function () {
        $c = new EvdsClient(apiKey: 'TESTKEY', mockMode: false);
        TestRunner::assertThrows(RuntimeException::class, function () use ($c) {
            // Network'e gitmeden önce parse hatası vermeli
            $c->fetchMonthlySeries('TP.FG.J0', 'invalid', '2026-01');
        });
    });
});

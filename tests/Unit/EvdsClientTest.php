<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\EvdsClient;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EvdsClientTest extends TestCase
{
    public function test_apikey_yoksa_mock_mode(): void
    {
        $c = new EvdsClient(apiKey: null);
        $this->assertTrue($c->isMockMode());
    }

    public function test_apikey_varsa_live_mode(): void
    {
        $c = new EvdsClient(apiKey: 'TESTKEY', mockMode: false);
        $this->assertFalse($c->isMockMode());
    }

    public function test_mock_seri_25_ay(): void
    {
        $c = new EvdsClient(apiKey: null);
        $series = $c->fetchMonthlySeries('TP.FG.J0', '2024-01', '2026-01');
        $this->assertCount(25, $series);
        $this->assertArrayHasKey('2024-01', $series);
        $this->assertArrayHasKey('2026-01', $series);
    }

    public function test_mock_seri_monoton_artar(): void
    {
        $c = new EvdsClient(apiKey: null);
        $series = $c->fetchMonthlySeries('TP.FG.J01', '2024-01', '2025-12');
        $values = array_values($series);
        for ($i = 1; $i < count($values); $i++) {
            $this->assertGreaterThan($values[$i - 1], $values[$i]);
        }
    }

    public function test_farkli_kodlar_farkli_baz(): void
    {
        $c = new EvdsClient(apiKey: null);
        $tufe  = $c->fetchMonthlySeries('TP.FG.J0', '2024-01', '2024-01')['2024-01'];
        $gida  = $c->fetchMonthlySeries('TP.FG.J01', '2024-01', '2024-01')['2024-01'];
        $yiufe = $c->fetchMonthlySeries('TP.FE.OKTG01', '2024-01', '2024-01')['2024-01'];
        $this->assertNotEquals($tufe, $gida);
        $this->assertNotEquals($tufe, $yiufe);
    }

    public function test_gecersiz_tarih_live_modda_hata(): void
    {
        $c = new EvdsClient(apiKey: 'TESTKEY', mockMode: false);
        $this->expectException(RuntimeException::class);
        $c->fetchMonthlySeries('TP.FG.J0', 'invalid', '2026-01');
    }
}

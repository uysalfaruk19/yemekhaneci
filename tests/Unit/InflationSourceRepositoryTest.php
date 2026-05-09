<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Repositories\InflationSourceRepository;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class InflationSourceRepositoryTest extends TestCase
{
    private string $tmpFile;

    protected function setUp(): void
    {
        $this->tmpFile = sys_get_temp_dir() . '/yh_test_repo_' . uniqid() . '.json';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tmpFile)) unlink($this->tmpFile);
    }

    public function test_resmi_4_kaynak_gorunur(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $codes = array_column($repo->all(), 'code');
        foreach (['tuik_tufe', 'tuik_tufe_gida', 'tuik_yiufe', 'enag_tufe'] as $expected) {
            $this->assertContains($expected, $codes);
        }
    }

    public function test_yeni_ozel_kaynak_olusturma(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $rec = $repo->createCustom([
            'code' => 'uysa_test', 'name' => 'UYSA Test', 'unit' => 'tl_kg',
            'color_hex' => '#abcdef', 'display_order' => 100,
        ], 'TESTUSER');
        $this->assertSame('uysa_test', $rec['code']);
        $this->assertSame('custom_admin', $rec['source_type']);
        $this->assertNotNull($repo->findCustom('uysa_test'));
    }

    public function test_rezerve_onek_reddedilir(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $this->expectException(RuntimeException::class);
        $repo->createCustom(['code' => 'tuik_yeni', 'name' => 'Çakışan'], 'X');
    }

    public function test_duplicate_kod_reddedilir(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $repo->createCustom(['code' => 'uysa_dup', 'name' => 'Test'], 'X');
        $this->expectException(RuntimeException::class);
        $repo->createCustom(['code' => 'uysa_dup', 'name' => 'Tekrar'], 'X');
    }

    public function test_aylik_veri_ekleme(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $repo->createCustom(['code' => 'uysa_v', 'name' => 'V'], 'OFU');
        $repo->addMonthlyValue('uysa_v', 2025, 1, 100.0, null, 'OFU');
        $repo->addMonthlyValue('uysa_v', 2025, 2, 105.0, null, 'OFU');
        $values = $repo->monthlyValues('uysa_v');
        $this->assertCount(2, $values);
        $this->assertEqualsWithDelta(105.0, $values['2025-02']['value'], 0.001);
    }

    public function test_aylik_veri_validasyonu(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $repo->createCustom(['code' => 'uysa_x', 'name' => 'X'], 'OFU');
        $this->expectException(RuntimeException::class);
        $repo->addMonthlyValue('uysa_x', 2002, 1, 50.0, null, 'X');
    }

    public function test_yearly_pct_hesaplanir(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $repo->createCustom(['code' => 'uysa_y', 'name' => 'Y'], 'OFU');
        $repo->addMonthlyValue('uysa_y', 2025, 1, 100.0, null, 'OFU');
        $repo->addMonthlyValue('uysa_y', 2026, 1, 121.0, null, 'OFU');
        $series = $repo->customMonthlySeries();
        $this->assertEqualsWithDelta(21.0, (float) $series['uysa_y']['2026-01']['yearly_pct'], 0.01);
    }

    public function test_aylik_veri_silme(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $repo->createCustom(['code' => 'uysa_del', 'name' => 'D'], 'OFU');
        $repo->addMonthlyValue('uysa_del', 2025, 1, 100.0, null, 'OFU');
        $repo->deleteMonthlyValue('uysa_del', '2025-01');
        $this->assertEmpty($repo->monthlyValues('uysa_del'));
    }

    public function test_kaynak_guncelleme(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $repo->createCustom(['code' => 'uysa_u', 'name' => 'Eski'], 'OFU');
        $updated = $repo->updateCustom('uysa_u', ['name' => 'Yeni'], 'OFU2');
        $this->assertSame('Yeni', $updated['name']);
        $this->assertSame('OFU2', $updated['updated_by']);
    }

    public function test_kaynak_silme(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $repo->createCustom(['code' => 'uysa_s', 'name' => 'S'], 'OFU');
        $repo->deleteCustom('uysa_s');
        $this->assertNull($repo->findCustom('uysa_s'));
    }

    public function test_resmi_aylik_set_get(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $repo->setOfficialMonthlyValue('tuik_tufe', '2025-01', 1234.5, 'evds:test');
        $repo->setOfficialMonthlyValue('tuik_tufe', '2025-02', 1267.4, 'evds:test');
        $values = $repo->officialMonthlyValues('tuik_tufe');
        $this->assertCount(2, $values);
        $series = $repo->officialMonthlySeries();
        $this->assertEqualsWithDelta(2.6651, (float) $series['tuik_tufe']['2025-02']['monthly_pct'], 0.01);
    }

    public function test_evds_run_meta(): void
    {
        $repo = new InflationSourceRepository($this->tmpFile);
        $repo->recordEvdsRun('success', 'Test çalışması');
        $meta = $repo->evdsRunMeta();
        $this->assertSame('success', $meta['last_status']);
        $this->assertStringContainsString('Test', (string) $meta['last_message']);
        $this->assertGreaterThanOrEqual(1, $meta['runs']);
    }
}

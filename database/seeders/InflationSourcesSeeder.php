<?php

declare(strict_types=1);

/**
 * Seeder — Resmî enflasyon kaynaklarının başlangıç verisi.
 * PRD Bölüm 25.3.1 · ADR-013b
 *
 * Bu kaynaklar admin panelinden silinemez (is_official=true);
 * sadece is_active toggle edilebilir.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InflationSourcesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $sources = [
            [
                'code'           => 'tuik_tufe',
                'name'           => 'TÜİK TÜFE Genel',
                'description'    => 'Türkiye İstatistik Kurumu — Tüketici Fiyat Endeksi (genel). Aylık olarak yayımlanır, baz dönem 2003=100.',
                'source_type'    => 'tuik_api',
                'tuik_evds_code' => 'TP.FG.J0',
                'base_period'    => '2003=100',
                'unit'           => 'index',
                'is_official'    => 1,
                'is_active'      => 1,
                'display_order'  => 10,
                'color_hex'      => '#6B1F2A',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'code'           => 'tuik_tufe_gida',
                'name'           => 'TÜİK Gıda Endeksi (TÜFE alt grubu)',
                'description'    => 'TÜİK TÜFE içinde "Gıda ve alkolsüz içecekler" alt kalemi. Catering ve yemek sektörü için en doğru ölçü.',
                'source_type'    => 'tuik_api',
                'tuik_evds_code' => 'TP.FG.J01',
                'base_period'    => '2003=100',
                'unit'           => 'index',
                'is_official'    => 1,
                'is_active'      => 1,
                'display_order'  => 20,
                'color_hex'      => '#C9A961',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'code'           => 'tuik_yiufe',
                'name'           => 'TÜİK Yİ-ÜFE (Eski TEFE)',
                'description'    => 'Yurt İçi Üretici Fiyat Endeksi. Toptan ve hammadde maliyetleri için referans.',
                'source_type'    => 'tuik_api',
                'tuik_evds_code' => 'TP.FE.OKTG01',
                'base_period'    => '2003=100',
                'unit'           => 'index',
                'is_official'    => 1,
                'is_active'      => 1,
                'display_order'  => 30,
                'color_hex'      => '#2A5C6B',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'code'           => 'enag_tufe',
                'name'           => 'ENAG TÜFE (Bağımsız)',
                'description'    => 'Enflasyon Araştırma Grubu — bağımsız akademisyenlerin hesapladığı alternatif TÜFE. Aylık manuel girilir (resmî API yok).',
                'source_type'    => 'enag_manual',
                'tuik_evds_code' => null,
                'base_period'    => '2020=100',
                'unit'           => 'index',
                'is_official'    => 1,
                'is_active'      => 1,
                'display_order'  => 40,
                'color_hex'      => '#7A2A2A',
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ];

        // upsert ile idempotent — yeniden seed çalıştırılırsa güncellenir
        DB::table('inflation_sources')->upsert(
            $sources,
            uniqueBy: ['code'],
            update: ['name', 'description', 'tuik_evds_code', 'base_period', 'unit', 'display_order', 'color_hex', 'updated_at']
        );
    }
}

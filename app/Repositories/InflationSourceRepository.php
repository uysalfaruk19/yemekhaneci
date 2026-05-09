<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\InflationSourceRepositoryInterface;
use RuntimeException;

/**
 * Enflasyon kaynağı deposu — Faz 0.5 demo (JSON dosya tabanlı).
 *
 * Faz 1.0a'da bu sınıf Eloquent destekli `InflationSourceModelRepository`
 * ile değiştirilecek; metod imzaları aynen korunacak.
 *
 * Resmî kaynaklar (TÜFE, Gıda, Yİ-ÜFE, ENAG) sabit (kod içinde tanımlı).
 * Özel kaynaklar (UYSA Et Endeksi vb.) `storage/inflation/data.json`'da
 * tutulur; aylık veri girişleri de aynı dosyaya yazılır.
 */
final class InflationSourceRepository implements InflationSourceRepositoryInterface
{
    private string $dataFile;

    /** Yeni özel kaynak oluştururken kabul edilmeyen kod ön ekleri (resmî yerleri rezerve). */
    public const RESERVED_PREFIXES = ['tuik_', 'enag_'];

    /** Geçerli birim seçenekleri. */
    public const UNITS = [
        'index'   => 'Endeks (baz=100)',
        'tl_kg'   => '₺ / kg',
        'tl_lt'   => '₺ / litre',
        'tl_unit' => '₺ / adet',
    ];

    public function __construct(?string $dataFile = null)
    {
        $this->dataFile = $dataFile ?? \app_path('storage/inflation/data.json');
        $this->ensureDataFile();
    }

    /**
     * Tüm kaynaklar (4 resmî + admin'in oluşturdukları), `display_order` sırasıyla.
     *
     * @return array<int, array<string,mixed>>
     */
    public function all(): array
    {
        $official = self::officialSources();
        $custom   = $this->readCustomSources();

        $merged = array_merge($official, array_values($custom));
        usort($merged, static fn(array $a, array $b) => ($a['display_order'] ?? 100) <=> ($b['display_order'] ?? 100));
        return $merged;
    }

    public function find(string $code): ?array
    {
        foreach ($this->all() as $src) {
            if ($src['code'] === $code) {
                return $src;
            }
        }
        return null;
    }

    public function findCustom(string $code): ?array
    {
        $custom = $this->readCustomSources();
        return $custom[$code] ?? null;
    }

    /**
     * Yeni özel kaynak oluşturur.
     *
     * @param array{code:string,name:string,description?:string,unit?:string,base_period?:string,color_hex?:string,display_order?:int} $data
     * @param string $createdBy  (admin username)
     * @return array<string,mixed>  Oluşturulan kayıt.
     * @throws RuntimeException
     */
    public function createCustom(array $data, string $createdBy): array
    {
        $code = $this->normalizeCode($data['code'] ?? '');
        if ($code === '') {
            throw new RuntimeException('Kaynak kodu boş olamaz.');
        }
        if ($this->isReservedCode($code)) {
            throw new RuntimeException("'{$code}' kodu rezerve. Resmî kaynaklarla çakışmasın diye 'tuik_' veya 'enag_' ile başlayamaz.");
        }
        if ($this->find($code) !== null) {
            throw new RuntimeException("'{$code}' kodu zaten kullanılıyor.");
        }

        $now = date('Y-m-d H:i:s');
        $record = [
            'id'             => time() * 1000 + random_int(0, 999),  // Faz 0.5 demo için pseudo-id
            'code'           => $code,
            'name'           => trim($data['name'] ?? ''),
            'description'    => trim((string) ($data['description'] ?? '')),
            'source_type'    => 'custom_admin',
            'tuik_evds_code' => null,
            'base_period'    => trim((string) ($data['base_period'] ?? '')) ?: ($code . '=100'),
            'unit'           => $data['unit']  ?? 'index',
            'is_official'    => false,
            'is_active'      => true,
            'display_order'  => (int) ($data['display_order'] ?? 100),
            'color_hex'      => $this->normalizeColor($data['color_hex'] ?? '#2A5C6B'),
            'created_by'     => $createdBy,
            'created_at'     => $now,
            'updated_at'     => $now,
            'monthly_values' => [],
        ];

        $this->mutate(static function (array &$store) use ($record): void {
            $store['custom_sources'][$record['code']] = $record;
        });

        return $record;
    }

    /**
     * @param array{name?:string,description?:string,unit?:string,base_period?:string,color_hex?:string,display_order?:int,is_active?:bool} $data
     */
    public function updateCustom(string $code, array $data, string $updatedBy): array
    {
        $existing = $this->findCustom($code);
        if (!$existing) {
            throw new RuntimeException("Kaynak bulunamadı veya resmî kaynak: {$code}");
        }

        $existing['name']          = trim($data['name'] ?? $existing['name']);
        $existing['description']   = trim((string) ($data['description'] ?? $existing['description']));
        $existing['unit']          = $data['unit'] ?? $existing['unit'];
        $existing['base_period']   = trim((string) ($data['base_period'] ?? $existing['base_period']));
        $existing['color_hex']     = $this->normalizeColor($data['color_hex'] ?? $existing['color_hex']);
        $existing['display_order'] = (int) ($data['display_order'] ?? $existing['display_order']);
        $existing['is_active']     = (bool) ($data['is_active'] ?? $existing['is_active']);
        $existing['updated_at']    = date('Y-m-d H:i:s');
        $existing['updated_by']    = $updatedBy;

        $this->mutate(static function (array &$store) use ($code, $existing): void {
            $store['custom_sources'][$code] = $existing;
        });

        return $existing;
    }

    public function deleteCustom(string $code): void
    {
        if (!$this->findCustom($code)) {
            throw new RuntimeException("Silinecek kaynak bulunamadı: {$code}");
        }
        $this->mutate(static function (array &$store) use ($code): void {
            unset($store['custom_sources'][$code]);
        });
    }

    /**
     * Bir özel kaynağa aylık endeks değeri ekler/günceller.
     */
    public function addMonthlyValue(string $code, int $year, int $month, float $value, ?string $notes, string $enteredBy): void
    {
        $existing = $this->findCustom($code);
        if (!$existing) {
            throw new RuntimeException('Sadece özel kaynaklara aylık veri eklenebilir.');
        }
        if ($year < 2003 || $year > 2099) {
            throw new RuntimeException('Yıl 2003-2099 aralığında olmalı.');
        }
        if ($month < 1 || $month > 12) {
            throw new RuntimeException('Ay 1-12 aralığında olmalı.');
        }
        if ($value <= 0) {
            throw new RuntimeException('Değer sıfırdan büyük olmalı.');
        }

        $key = sprintf('%04d-%02d', $year, $month);

        $this->mutate(static function (array &$store) use ($code, $key, $value, $notes, $enteredBy): void {
            $store['custom_sources'][$code]['monthly_values'][$key] = [
                'value'      => $value,
                'notes'      => $notes,
                'entered_by' => $enteredBy,
                'entered_at' => date('Y-m-d H:i:s'),
            ];
            $store['custom_sources'][$code]['updated_at'] = date('Y-m-d H:i:s');
        });
    }

    public function deleteMonthlyValue(string $code, string $periodKey): void
    {
        $this->mutate(static function (array &$store) use ($code, $periodKey): void {
            unset($store['custom_sources'][$code]['monthly_values'][$periodKey]);
            if (isset($store['custom_sources'][$code])) {
                $store['custom_sources'][$code]['updated_at'] = date('Y-m-d H:i:s');
            }
        });
    }

    /**
     * Bir kaynağın tüm aylık değerleri (date-key sıralı).
     *
     * @return array<string, array{value:float, notes:?string, entered_by:string, entered_at:string}>
     */
    public function monthlyValues(string $code): array
    {
        $custom = $this->findCustom($code);
        if (!$custom) return [];
        $values = $custom['monthly_values'] ?? [];
        ksort($values);
        return $values;
    }

    /**
     * Sentetik mock veriyi merge etmeden, sadece custom kaynaklara girilmiş aylık değerleri döner.
     * `InflationCalculator::mockIndices()` bu veriyi okuyup kendi serisine ekler.
     *
     * @return array<string, array<string, array{value:float, monthly_pct:?float, yearly_pct:?float}>>
     */
    public function customMonthlySeries(): array
    {
        $custom = $this->readCustomSources();
        $out = [];
        foreach ($custom as $code => $rec) {
            $values = $rec['monthly_values'] ?? [];
            ksort($values);
            $previous = null;
            $series = [];
            foreach ($values as $period => $entry) {
                $series[$period] = [
                    'value'        => (float) $entry['value'],
                    'monthly_pct'  => $previous === null ? null : round((($entry['value'] / $previous) - 1) * 100, 4),
                    'yearly_pct'   => null,
                ];
                $previous = (float) $entry['value'];
            }
            // İkinci pas: yıllık değişim
            foreach ($series as $period => $row) {
                [$y, $m] = array_map('intval', explode('-', $period));
                $prevYear = sprintf('%04d-%02d', $y - 1, $m);
                if (isset($series[$prevYear])) {
                    $series[$period]['yearly_pct'] = round((($row['value'] / $series[$prevYear]['value']) - 1) * 100, 4);
                }
            }
            $out[$code] = $series;
        }
        return $out;
    }

    // ---- Resmî kaynaklar (sabit) -----------------------------------------------------------

    /**
     * @return array<int, array<string,mixed>>
     */
    public static function officialSources(): array
    {
        return [
            [
                'code' => 'tuik_tufe',
                'name' => 'TÜİK TÜFE Genel',
                'description' => 'Türkiye İstatistik Kurumu — Tüketici Fiyat Endeksi (genel). Aylık olarak yayımlanır.',
                'source_type' => 'tuik_api',
                'tuik_evds_code' => 'TP.FG.J0',
                'base_period' => '2003=100',
                'unit' => 'index',
                'is_official' => true,
                'is_active' => true,
                'display_order' => 10,
                'color_hex' => '#6B1F2A',
            ],
            [
                'code' => 'tuik_tufe_gida',
                'name' => 'TÜİK Gıda Endeksi (TÜFE alt grubu)',
                'description' => 'TÜİK TÜFE içinde "Gıda ve alkolsüz içecekler" alt kalemi. Catering ve yemek için en doğru ölçü.',
                'source_type' => 'tuik_api',
                'tuik_evds_code' => 'TP.FG.J01',
                'base_period' => '2003=100',
                'unit' => 'index',
                'is_official' => true,
                'is_active' => true,
                'display_order' => 20,
                'color_hex' => '#C9A961',
            ],
            [
                'code' => 'tuik_yiufe',
                'name' => 'TÜİK Yİ-ÜFE (eski TEFE)',
                'description' => 'Yurt İçi Üretici Fiyat Endeksi. Toptan ve hammadde maliyetleri için referans.',
                'source_type' => 'tuik_api',
                'tuik_evds_code' => 'TP.FE.OKTG01',
                'base_period' => '2003=100',
                'unit' => 'index',
                'is_official' => true,
                'is_active' => true,
                'display_order' => 30,
                'color_hex' => '#2A5C6B',
            ],
            [
                'code' => 'enag_tufe',
                'name' => 'ENAG TÜFE (bağımsız)',
                'description' => 'Enflasyon Araştırma Grubu — bağımsız akademisyenlerin alternatif TÜFE\'si. Aylık manuel girilir.',
                'source_type' => 'enag_manual',
                'tuik_evds_code' => null,
                'base_period' => '2020=100',
                'unit' => 'index',
                'is_official' => true,
                'is_active' => true,
                'display_order' => 40,
                'color_hex' => '#7A2A2A',
            ],
        ];
    }

    // ---- Yardımcılar ------------------------------------------------------------------------

    public function isReservedCode(string $code): bool
    {
        foreach (self::RESERVED_PREFIXES as $prefix) {
            if (str_starts_with($code, $prefix)) return true;
        }
        return false;
    }

    private function normalizeCode(string $raw): string
    {
        $raw = strtolower(trim($raw));
        $raw = preg_replace('/[^a-z0-9_]+/', '_', $raw) ?? '';
        $raw = preg_replace('/_+/', '_', $raw) ?? '';
        return trim($raw, '_');
    }

    private function normalizeColor(string $raw): string
    {
        $raw = strtoupper(trim($raw));
        if (preg_match('/^#?([0-9A-F]{6})$/', $raw, $m)) {
            return '#' . $m[1];
        }
        return '#2A5C6B';
    }

    /**
     * @return array<string, array<string,mixed>>
     */
    private function readCustomSources(): array
    {
        $raw = file_get_contents($this->dataFile);
        if ($raw === false || trim($raw) === '') {
            return [];
        }
        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new RuntimeException('storage/inflation/data.json bozuk: ' . $e->getMessage());
        }
        return $data['custom_sources'] ?? [];
    }

    /**
     * Veri dosyasını flock ile güvenli kilitleyip mutate ettir.
     *
     * @param callable(array<string,mixed>): void $mutator
     */
    private function mutate(callable $mutator): void
    {
        $fp = fopen($this->dataFile, 'c+');
        if ($fp === false) {
            throw new RuntimeException('Veri dosyası açılamadı: ' . $this->dataFile);
        }
        try {
            if (!flock($fp, LOCK_EX)) {
                throw new RuntimeException('Veri dosyası kilitlenemedi.');
            }

            $contents = stream_get_contents($fp) ?: '';
            $store = $contents === '' ? ['custom_sources' => []] : (json_decode($contents, true) ?: ['custom_sources' => []]);

            $mutator($store);

            $payload = json_encode($store, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            if ($payload === false) {
                throw new RuntimeException('JSON kodlama başarısız.');
            }

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $payload);
            fflush($fp);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    private function ensureDataFile(): void
    {
        $dir = dirname($this->dataFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, json_encode(['custom_sources' => new \stdClass()], JSON_PRETTY_PRINT));
        }
    }

    // ---- Resmî kaynak aylık değerleri (EVDS / ENAG manuel) -----------------------------------

    /**
     * Resmî bir kaynağın bir aylık değerini set eder (EVDS fetcher veya ENAG manuel giriş).
     */
    public function setOfficialMonthlyValue(string $code, string $period, float $value, string $enteredBy, ?string $notes = null): void
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            throw new RuntimeException('Period biçimi YYYY-MM olmalı.');
        }

        $this->mutate(static function (array &$store) use ($code, $period, $value, $enteredBy, $notes): void {
            if (!isset($store['official_values'])) $store['official_values'] = [];
            if (!isset($store['official_values'][$code])) $store['official_values'][$code] = [];
            $store['official_values'][$code][$period] = [
                'value'      => $value,
                'notes'      => $notes,
                'entered_by' => $enteredBy,
                'entered_at' => date('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * Bir resmî kaynağın tüm aylık değerleri (varsa).
     *
     * @return array<string, array{value:float, notes:?string, entered_by:string, entered_at:string}>
     */
    public function officialMonthlyValues(string $code): array
    {
        $raw = file_get_contents($this->dataFile);
        if ($raw === false || trim($raw) === '') return [];
        $data = json_decode($raw, true) ?: [];
        $values = $data['official_values'][$code] ?? [];
        ksort($values);
        return $values;
    }

    /**
     * EVDS son çalışma damgaları (admin paneli için).
     *
     * @return array{last_run_at:?string, last_status:?string, last_message:?string, runs:int}
     */
    public function evdsRunMeta(): array
    {
        $raw = file_get_contents($this->dataFile);
        $data = $raw ? (json_decode($raw, true) ?: []) : [];
        return $data['evds_run'] ?? ['last_run_at' => null, 'last_status' => null, 'last_message' => null, 'runs' => 0];
    }

    public function recordEvdsRun(string $status, string $message): void
    {
        $this->mutate(static function (array &$store) use ($status, $message): void {
            $store['evds_run'] = [
                'last_run_at'  => date('Y-m-d H:i:s'),
                'last_status'  => $status,
                'last_message' => $message,
                'runs'         => (int) ($store['evds_run']['runs'] ?? 0) + 1,
            ];
        });
    }

    /**
     * Calculator için: tüm resmî kaynakların DB'den gelen aylık serileri.
     *
     * @return array<string, array<string, array{value:float, monthly_pct:?float, yearly_pct:?float}>>
     */
    public function officialMonthlySeries(): array
    {
        $raw = file_get_contents($this->dataFile);
        $data = $raw ? (json_decode($raw, true) ?: []) : [];
        $official = $data['official_values'] ?? [];

        $out = [];
        foreach ($official as $code => $values) {
            ksort($values);
            $previous = null;
            $series = [];
            foreach ($values as $period => $entry) {
                $val = (float) $entry['value'];
                $series[$period] = [
                    'value'        => $val,
                    'monthly_pct'  => $previous === null ? null : round((($val / $previous) - 1) * 100, 4),
                    'yearly_pct'   => null,
                ];
                $previous = $val;
            }
            foreach ($series as $period => $row) {
                [$y, $m] = array_map('intval', explode('-', $period));
                $prevYear = sprintf('%04d-%02d', $y - 1, $m);
                if (isset($series[$prevYear])) {
                    $series[$period]['yearly_pct'] = round((($row['value'] / $series[$prevYear]['value']) - 1) * 100, 4);
                }
            }
            $out[$code] = $series;
        }
        return $out;
    }
}

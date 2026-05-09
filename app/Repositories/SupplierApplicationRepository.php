<?php

declare(strict_types=1);

namespace App\Repositories;

use RuntimeException;

/**
 * Yemekçi başvuruları (PRD §4 — KYC öncesi). Faz 0.5 demo.
 * Faz 1.0a'da `supplier_applications` tablosuna taşınacak.
 */
final class SupplierApplicationRepository
{
    private string $dataFile;

    public function __construct(?string $dataFile = null)
    {
        $this->dataFile = $dataFile ?? \app_path('storage/applications/suppliers.json');
        $dir = dirname($this->dataFile);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (!file_exists($this->dataFile)) file_put_contents($this->dataFile, '[]');
    }

    /** Statüler: pending (başvuru), approved (onaylı), active (yayında), suspended (askıda), rejected (red), manual (admin'in eklediği) */
    public const STATUSES = ['pending', 'approved', 'active', 'suspended', 'rejected', 'manual'];

    public function create(array $data): array
    {
        $now = date('Y-m-d H:i:s');
        $rand = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $record = [
            'id'                => (int) (microtime(true) * 1000),
            'reference'         => 'YHCBV-' . date('ym') . '-' . $rand,
            'company_name'      => trim($data['company_name'] ?? ''),
            'tax_number'        => trim($data['tax_number'] ?? ''),
            'contact_name'      => trim($data['contact_name'] ?? ''),
            'contact_email'     => trim($data['contact_email'] ?? ''),
            'contact_phone'     => trim($data['contact_phone'] ?? ''),
            'city'              => trim($data['city'] ?? ''),
            'district'          => trim($data['district'] ?? ''),
            'years_in_business' => (int) ($data['years_in_business'] ?? 0),
            'daily_capacity'    => (int) ($data['daily_capacity'] ?? 0),
            'service_areas'     => $data['service_areas'] ?? [],
            'certifications'    => $data['certifications'] ?? [],
            'website'           => trim($data['website'] ?? ''),
            'notes'             => trim($data['notes'] ?? ''),
            'rating'            => (float) ($data['rating'] ?? 4.5),
            'anonymous_code'    => $data['anonymous_code'] ?? null,
            'pricing'           => $data['pricing'] ?? self::defaultPricing(),
            'status'            => $data['status'] ?? 'pending',
            'approved_at'       => null,
            'approved_by'       => null,
            'ip_address'        => $data['ip_address'] ?? '',
            'user_agent'        => mb_substr((string) ($data['user_agent'] ?? ''), 0, 500),
            'created_at'        => $now,
            'updated_at'        => $now,
        ];

        $this->mutate(static function (array &$store) use ($record): void {
            $store[] = $record;
            if (count($store) > 5000) $store = array_slice($store, -5000);
        });

        return $record;
    }

    public static function defaultPricing(): array
    {
        return [
            'ekonomik_per_meal' => 110,
            'genel_per_meal'    => 145,
            'premium_per_meal'  => 195,
            'salad_per_extra'   => 4,
            'dessert_addon'     => 8,
            'drinks_both_addon' => 5,
            'personnel_addon'   => 25,
            'equipment_addon'   => 18,
            'saturday_partial'  => 5,
            'saturday_yes'      => 10,
            'notes'             => '',
        ];
    }

    public function find(int $id): ?array
    {
        foreach ($this->readAll() as $row) {
            if ((int) ($row['id'] ?? 0) === $id) return $row;
        }
        return null;
    }

    /**
     * Yemekçi kaydını günceller — status, pricing, profil alanlarına izin verir.
     */
    public function update(int $id, array $changes, string $updatedBy = 'system'): ?array
    {
        $found = null;
        $this->mutate(static function (array &$store) use ($id, $changes, $updatedBy, &$found): void {
            foreach ($store as $i => $row) {
                if ((int) ($row['id'] ?? 0) !== $id) continue;
                foreach ($changes as $k => $v) {
                    if ($k === 'pricing' && is_array($v)) {
                        $store[$i]['pricing'] = array_merge($row['pricing'] ?? self::defaultPricing(), $v);
                    } else {
                        $store[$i][$k] = $v;
                    }
                }
                $store[$i]['updated_at'] = date('Y-m-d H:i:s');
                $store[$i]['updated_by'] = $updatedBy;
                $found = $store[$i];
                return;
            }
        });
        return $found;
    }

    public function setStatus(int $id, string $status, string $actor = 'system'): ?array
    {
        if (!in_array($status, self::STATUSES, true)) {
            throw new RuntimeException("Geçersiz statü: {$status}");
        }
        $changes = ['status' => $status];
        if ($status === 'active' || $status === 'approved') {
            $changes['approved_at'] = date('Y-m-d H:i:s');
            $changes['approved_by'] = $actor;
        }
        return $this->update($id, $changes, $actor);
    }

    public function delete(int $id): void
    {
        $this->mutate(static function (array &$store) use ($id): void {
            foreach ($store as $i => $row) {
                if ((int) ($row['id'] ?? 0) === $id) {
                    array_splice($store, $i, 1);
                    return;
                }
            }
        });
    }

    /** @return array<int,array<string,mixed>> */
    public function recent(int $limit = 100): array
    {
        $all = $this->readAll();
        usort($all, static fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        return array_slice($all, 0, $limit);
    }

    /** @return array<int,array<string,mixed>> */
    public function findByStatus(string $status): array
    {
        return array_values(array_filter(
            $this->readAll(),
            static fn(array $r) => ($r['status'] ?? '') === $status
        ));
    }

    /**
     * Anonim yemekçi listesi için aktif yemekçileri filtrele.
     * Şehir tam eşleşmiyorsa (veya hiç yemekçi yoksa) tüm aktif yemekçileri döner.
     *
     * @return array<int, array<string,mixed>>
     */
    public function activeForCity(string $city, int $limit = 5): array
    {
        $all = $this->readAll();
        $active = array_filter($all, static fn(array $r) => in_array($r['status'] ?? '', ['active', 'manual'], true));
        $cityLower = mb_strtolower(trim($city));

        // Önce şehir eşleşenler
        $matched = array_values(array_filter($active, static function (array $r) use ($cityLower) {
            $rowCity = mb_strtolower($r['city'] ?? '');
            $areas = array_map('mb_strtolower', (array) ($r['service_areas'] ?? []));
            return $rowCity === $cityLower || in_array($cityLower, $areas, true);
        }));

        $pool = count($matched) >= 3 ? $matched : array_values($active);
        return array_slice($pool, 0, $limit);
    }

    public function totalCount(): int
    {
        return count($this->readAll());
    }

    public function pendingCount(): int
    {
        return count($this->findByStatus('pending'));
    }

    public function activeCount(): int
    {
        $all = $this->readAll();
        return count(array_filter($all, static fn(array $r) => in_array($r['status'] ?? '', ['active', 'manual'], true)));
    }

    /**
     * Aktif yemekçi sayısına göre yeni anonim kod üret (A, B, C ... AA, AB, ...).
     */
    public function nextAnonymousCode(): string
    {
        $existingCodes = array_column($this->readAll(), 'anonymous_code');
        $existingCodes = array_filter($existingCodes, static fn($c) => is_string($c) && $c !== '');

        $i = 0;
        while (true) {
            $code = self::indexToCode($i);
            if (!in_array('Yemekçi ' . $code, $existingCodes, true)) {
                return 'Yemekçi ' . $code;
            }
            $i++;
            if ($i > 9999) break;
        }
        return 'Yemekçi ?';
    }

    private static function indexToCode(int $i): string
    {
        // 0→A, 1→B, ..., 25→Z, 26→AA, 27→AB, ...
        $code = '';
        $i++;
        while ($i > 0) {
            $i--;
            $code = chr(65 + ($i % 26)) . $code;
            $i = intdiv($i, 26);
        }
        return $code;
    }

    private function readAll(): array
    {
        $raw = file_get_contents($this->dataFile);
        if ($raw === false || trim($raw) === '') return [];
        return json_decode($raw, true) ?: [];
    }

    private function mutate(callable $mutator): void
    {
        $fp = fopen($this->dataFile, 'c+');
        if ($fp === false) throw new RuntimeException('suppliers.json açılamadı.');
        try {
            flock($fp, LOCK_EX);
            $contents = stream_get_contents($fp) ?: '[]';
            $store = json_decode($contents, true) ?: [];
            $mutator($store);
            ftruncate($fp, 0); rewind($fp);
            fwrite($fp, (string) json_encode($store, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            fflush($fp);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}

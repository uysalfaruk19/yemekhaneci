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

    public function create(array $data): array
    {
        $now = date('Y-m-d H:i:s');
        $rand = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $record = [
            'id'              => (int) (microtime(true) * 1000),
            'reference'       => 'YHCBV-' . date('ym') . '-' . $rand,
            'company_name'    => trim($data['company_name'] ?? ''),
            'tax_number'      => trim($data['tax_number'] ?? ''),
            'contact_name'    => trim($data['contact_name'] ?? ''),
            'contact_email'   => trim($data['contact_email'] ?? ''),
            'contact_phone'   => trim($data['contact_phone'] ?? ''),
            'city'            => trim($data['city'] ?? ''),
            'district'        => trim($data['district'] ?? ''),
            'years_in_business'=> (int) ($data['years_in_business'] ?? 0),
            'daily_capacity'  => (int) ($data['daily_capacity'] ?? 0),
            'service_areas'   => $data['service_areas'] ?? [],
            'certifications'  => $data['certifications'] ?? [],
            'website'         => trim($data['website'] ?? ''),
            'notes'           => trim($data['notes'] ?? ''),
            'ip_address'      => $data['ip_address'] ?? '',
            'user_agent'      => mb_substr((string) ($data['user_agent'] ?? ''), 0, 500),
            'status'          => 'pending',
            'created_at'      => $now,
        ];

        $this->mutate(static function (array &$store) use ($record): void {
            $store[] = $record;
            if (count($store) > 5000) $store = array_slice($store, -5000);
        });

        return $record;
    }

    public function recent(int $limit = 100): array
    {
        $all = $this->readAll();
        usort($all, static fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        return array_slice($all, 0, $limit);
    }

    public function totalCount(): int
    {
        return count($this->readAll());
    }

    public function pendingCount(): int
    {
        return count(array_filter($this->readAll(), static fn(array $r) => ($r['status'] ?? '') === 'pending'));
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
            fwrite($fp, (string) json_encode($store, JSON_UNESCAPED_UNICODE));
            fflush($fp);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}

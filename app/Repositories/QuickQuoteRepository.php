<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\QuickQuoteRepositoryInterface;
use RuntimeException;

/**
 * Hızlı teklif (60 saniye) talepleri için file-backed repo (Faz 3 öne çekme).
 * Faz 1+ DB şemasında `requests` + `request_items` tablolarına taşınacak.
 */
final class QuickQuoteRepository implements QuickQuoteRepositoryInterface
{
    private string $dataFile;

    public function __construct(?string $dataFile = null)
    {
        $this->dataFile = $dataFile ?? \app_path('storage/quotes/quick_quotes.json');
        $dir = dirname($this->dataFile);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, '[]');
        }
    }

    /**
     * @param array{
     *   guest_count:int, meal_type:string, event_date:string,
     *   city:string, district:?string, contact_name:?string,
     *   contact_email:?string, contact_phone:?string, notes:?string,
     *   ip_address:string, user_agent:string
     * } $data
     */
    public function create(array $data): array
    {
        $now = date('Y-m-d H:i:s');
        $record = [
            'id'             => (int) (microtime(true) * 1000),
            'reference'      => self::generateReference(),
            'guest_count'    => (int) $data['guest_count'],
            'meal_type'      => $data['meal_type'],
            'event_date'     => $data['event_date'],
            'city'           => trim($data['city']),
            'district'       => trim((string) ($data['district'] ?? '')),
            'contact_name'   => trim((string) ($data['contact_name'] ?? '')),
            'contact_email'  => trim((string) ($data['contact_email'] ?? '')),
            'contact_phone'  => trim((string) ($data['contact_phone'] ?? '')),
            'notes'          => trim((string) ($data['notes'] ?? '')),
            'ip_address'     => $data['ip_address'] ?? '',
            'user_agent'     => mb_substr((string) ($data['user_agent'] ?? ''), 0, 500),
            'status'         => 'new',
            'created_at'     => $now,
        ];

        $this->mutate(static function (array &$store) use ($record): void {
            $store[] = $record;
            if (count($store) > 10000) $store = array_slice($store, -10000);
        });

        return $record;
    }

    /** @return array<int, array<string,mixed>>  En yeni en başta. */
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

    /** Geçen 7 gün içinde gelen talep. */
    public function last7DaysCount(): int
    {
        $cutoff = strtotime('-7 days');
        return count(array_filter(
            $this->readAll(),
            static fn(array $r) => strtotime($r['created_at']) >= $cutoff
        ));
    }

    public static function generateReference(): string
    {
        // YHC-YYMM-XXXX
        $rand = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        return 'YHC-' . date('ym') . '-' . $rand;
    }

    /** @return array<int, array<string,mixed>> */
    private function readAll(): array
    {
        $raw = file_get_contents($this->dataFile);
        if ($raw === false || trim($raw) === '') return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /** @param callable(array<int, array<string,mixed>>): void $mutator */
    private function mutate(callable $mutator): void
    {
        $fp = fopen($this->dataFile, 'c+');
        if ($fp === false) throw new RuntimeException('quick_quotes.json açılamadı.');
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

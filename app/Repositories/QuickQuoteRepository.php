<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\QuickQuoteRepositoryInterface;
use RuntimeException;

/**
 * Hızlı teklif (9 soruluk wizard) talepleri — PRD §7.2.
 * Faz 1+ DB şemasında `requests` + `request_items` tablolarına taşınacak.
 *
 * Şema PRD'ye göre genişletildi:
 *   guest_count, meals{ogle,aksam,kumanya}, menu{...}, segment,
 *   location{city,district,address,coords?}, personnel{...}, equipment{...},
 *   saturday, notes{tags[],text}, contact{...}, kvkk
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

    public function create(array $data): array
    {
        $now = date('Y-m-d H:i:s');

        $meals = $data['meals'] ?? ['ogle' => 0, 'aksam' => 0, 'kumanya' => 0];
        $primaryMeal = $meals ? array_keys($meals, max($meals))[0] ?? 'ogle' : 'ogle';

        $record = [
            'id'            => (int) (microtime(true) * 1000),
            'reference'     => self::generateReference(),
            'guest_count'   => (int) ($data['guest_count'] ?? 0),
            'meal_count'    => (int) ($data['meal_count'] ?? 1),

            // PRD §7.2 — 9 soruluk akış
            'meals'         => array_map('intval', $meals),
            'menu'          => $data['menu'] ?? [],
            'segment'       => $data['segment'] ?? 'genel',
            'location'      => $data['location'] ?? [],
            'personnel'     => $data['personnel'] ?? ['enabled' => false],
            'equipment'     => $data['equipment'] ?? [
                'has_existing' => '',
                'requested'    => '',
            ],
            'saturday'      => $data['saturday'] ?? 'no',
            'notes'         => $data['notes'] ?? ['tags' => [], 'text' => null],

            // İletişim — opsiyonel, "Detaylı teklif al" sonrası dolar
            'contact_name'  => trim((string) ($data['contact_name'] ?? '')),
            'company_name'  => trim((string) ($data['company_name'] ?? '')),
            'contact_email' => trim((string) ($data['contact_email'] ?? '')),
            'contact_phone' => trim((string) ($data['contact_phone'] ?? '')),
            'kvkk_accepted_at' => !empty($data['kvkk']) ? $now : null,

            // Tahmini fiyat (sonuç ekranında gösterilen)
            'estimated_price_per_meal' => $data['estimated_price_per_meal'] ?? null,
            'estimated_monthly_total'  => $data['estimated_monthly_total']  ?? null,

            // Geriye uyumluluk
            'meal_type'     => $primaryMeal,
            'event_date'    => $data['event_date'] ?? null,
            'city'          => $data['location']['city'] ?? '',
            'district'      => $data['location']['district'] ?? '',

            'ip_address'    => $data['ip_address'] ?? '',
            'user_agent'    => mb_substr((string) ($data['user_agent'] ?? ''), 0, 500),
            'status'        => 'new',
            'created_at'    => $now,
        ];

        $this->mutate(static function (array &$store) use ($record): void {
            $store[] = $record;
            if (count($store) > 10000) $store = array_slice($store, -10000);
        });

        return $record;
    }

    /**
     * Mevcut talebe iletişim bilgileri ekle (Detaylı teklif al akışı).
     */
    public function attachContact(string $reference, array $contact): ?array
    {
        $found = null;
        $this->mutate(static function (array &$store) use ($reference, $contact, &$found): void {
            foreach ($store as $i => $row) {
                if (($row['reference'] ?? '') === $reference) {
                    $store[$i]['contact_name']     = trim((string) ($contact['contact_name'] ?? ''));
                    $store[$i]['company_name']     = trim((string) ($contact['company_name'] ?? ''));
                    $store[$i]['contact_email']    = trim((string) ($contact['contact_email'] ?? ''));
                    $store[$i]['contact_phone']    = trim((string) ($contact['contact_phone'] ?? ''));
                    $store[$i]['kvkk_accepted_at'] = !empty($contact['kvkk']) ? date('Y-m-d H:i:s') : null;
                    $store[$i]['status']           = 'contacted';
                    $found = $store[$i];
                    return;
                }
            }
        });
        return $found;
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
        $rand = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        return 'YHC-' . date('ym') . '-' . $rand;
    }

    private function readAll(): array
    {
        $raw = file_get_contents($this->dataFile);
        if ($raw === false || trim($raw) === '') return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

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

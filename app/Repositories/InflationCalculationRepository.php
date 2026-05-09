<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * `inflation_calculations` tablosunun JSON-file karşılığı (Faz 0.5).
 * KVKK uyumlu lead capture + analytics için tüm hesaplama sorguları burada saklanır.
 *
 * Faz 1.0a'da Eloquent `App\Models\InflationCalculation`'a taşınacak.
 */
final class InflationCalculationRepository
{
    private string $dataFile;

    public function __construct(?string $dataFile = null)
    {
        $this->dataFile = $dataFile ?? \app_path('storage/inflation/calculations.json');
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, '[]');
        }
    }

    /**
     * Yeni hesaplama kaydı ekler ve oluşturulan kaydı döner.
     *
     * @param array{
     *   source_code:string, start_date:string, start_price:float,
     *   end_date:string, end_price:float, change_pct:float,
     *   email:?string, kvkk_accepted:bool,
     *   ip_address:string, user_agent:string,
     *   panel_origin:string
     * } $data
     */
    public function create(array $data): array
    {
        $now = date('Y-m-d H:i:s');
        $record = [
            'id'                => (int) (microtime(true) * 1000),
            'source_code'       => $data['source_code'],
            'start_date'        => $data['start_date'],
            'start_price'       => (float) $data['start_price'],
            'end_date'          => $data['end_date'],
            'end_price'         => (float) $data['end_price'],
            'change_pct'        => (float) $data['change_pct'],
            'email'             => $data['email'] ?? null,
            'kvkk_accepted_at'  => !empty($data['kvkk_accepted']) ? $now : null,
            'ip_address'        => $data['ip_address'] ?? '',
            'user_agent'        => mb_substr((string) ($data['user_agent'] ?? ''), 0, 500),
            'panel_origin'      => $data['panel_origin'] ?? 'public',
            'created_at'        => $now,
        ];

        $this->mutate(static function (array &$store) use ($record): void {
            $store[] = $record;
            // Son 10000 kaydı tut (demo için yeterli; production'da DB)
            if (count($store) > 10000) {
                $store = array_slice($store, -10000);
            }
        });

        return $record;
    }

    /**
     * Lead'ler (e-posta'sı dolu kayıtlar), en yeni en üstte.
     *
     * @return array<int, array<string,mixed>>
     */
    public function leads(int $limit = 100): array
    {
        $all = $this->readAll();
        $leads = array_filter($all, static fn(array $r) => !empty($r['email']) && !empty($r['kvkk_accepted_at']));
        usort($leads, static fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        return array_slice($leads, 0, $limit);
    }

    public function totalCount(): int
    {
        return count($this->readAll());
    }

    public function leadCount(): int
    {
        return count(array_filter(
            $this->readAll(),
            static fn(array $r) => !empty($r['email']) && !empty($r['kvkk_accepted_at'])
        ));
    }

    /** @return array<string, int>  panel_origin => count */
    public function countByPanel(): array
    {
        $out = ['public' => 0, 'supplier' => 0, 'admin' => 0];
        foreach ($this->readAll() as $r) {
            $p = $r['panel_origin'] ?? 'public';
            $out[$p] = ($out[$p] ?? 0) + 1;
        }
        return $out;
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
        if ($fp === false) {
            throw new \RuntimeException('calculations.json açılamadı.');
        }
        try {
            flock($fp, LOCK_EX);
            $contents = stream_get_contents($fp) ?: '[]';
            $store = json_decode($contents, true);
            if (!is_array($store)) $store = [];
            $mutator($store);
            $payload = json_encode($store, JSON_UNESCAPED_UNICODE);
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, (string) $payload);
            fflush($fp);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}

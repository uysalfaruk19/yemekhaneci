<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Repositories\InflationSourceRepository;
use App\Services\EvdsClient;
use RuntimeException;

/**
 * Aylık enflasyon endeksi senkronizasyonu (PRD §25.5, Faz 0.5.7-8).
 *
 * Çalışma yöntemleri:
 * 1) Cron: her ayın 5'i 03:00 (TR) — `php artisan schedule:run` (Faz 1.0a sonrası)
 * 2) Manuel: admin paneli → "EVDS'i şimdi tetikle" butonu (Faz 0.5)
 *
 * Mock mode: API key yoksa veya `EVDS_MOCK=true` ise sentetik veri yazılır.
 */
final class FetchInflationIndicesJob
{
    private InflationSourceRepository $repo;
    private EvdsClient $client;
    private string $triggeredBy;

    public function __construct(
        ?InflationSourceRepository $repo = null,
        ?EvdsClient $client = null,
        string $triggeredBy = 'cron'
    ) {
        $this->repo        = $repo   ?? new InflationSourceRepository();
        $apiKey            = getenv('EVDS_API_KEY') ?: null;
        $mock              = getenv('EVDS_MOCK') === 'true' || $apiKey === null;
        $this->client      = $client ?? new EvdsClient($apiKey, $mock);
        $this->triggeredBy = $triggeredBy;
    }

    /**
     * @param string $startMonth YYYY-MM (varsayılan: 24 ay önce)
     * @param string $endMonth   YYYY-MM (varsayılan: bu ay)
     * @return array{ok:bool, mode:string, fetched:int, sources:array<int,array{code:string, count:int, error:?string}>, message:string}
     */
    public function run(?string $startMonth = null, ?string $endMonth = null): array
    {
        $start = $startMonth ?? date('Y-m', strtotime('-24 months'));
        $end   = $endMonth   ?? date('Y-m');

        $report = [];
        $totalCount = 0;
        $hadError = false;
        $mode = $this->client->isMockMode() ? 'mock' : 'live';

        // Sadece tuik_api kaynaklarını çek
        $tuikSources = array_filter(
            InflationSourceRepository::officialSources(),
            static fn(array $s) => ($s['source_type'] ?? '') === 'tuik_api' && !empty($s['tuik_evds_code'])
        );

        foreach ($tuikSources as $src) {
            $code = $src['code'];
            $evdsCode = $src['tuik_evds_code'];
            try {
                $series = $this->client->fetchMonthlySeries($evdsCode, $start, $end);
                foreach ($series as $period => $value) {
                    $this->repo->setOfficialMonthlyValue($code, $period, $value, "evds:{$this->triggeredBy}", "Çekim modu: {$mode}");
                }
                $report[] = ['code' => $code, 'count' => count($series), 'error' => null];
                $totalCount += count($series);
            } catch (\Throwable $e) {
                $hadError = true;
                $report[] = ['code' => $code, 'count' => 0, 'error' => $e->getMessage()];
            }
        }

        $message = $hadError
            ? sprintf('%s modunda %d kayıt yazıldı, bazı kaynaklarda hata alındı.', strtoupper($mode), $totalCount)
            : sprintf('%s modunda %d kayıt başarıyla yazıldı.', strtoupper($mode), $totalCount);

        $this->repo->recordEvdsRun($hadError ? 'partial' : 'success', $message);

        return [
            'ok'      => !$hadError,
            'mode'    => $mode,
            'fetched' => $totalCount,
            'sources' => $report,
            'message' => $message,
        ];
    }
}

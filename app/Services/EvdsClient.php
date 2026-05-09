<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * TCMB EVDS API istemcisi (PRD §25.5).
 *
 * EVDS endpoint:
 *   https://evds2.tcmb.gov.tr/service/evds/series={code}&startDate={dd-MM-yyyy}&endDate={dd-MM-yyyy}&type=json&key={api_key}
 *
 * Faz 0.5: API key olmadan çalışmak için mock mode (`EVDS_MOCK=true`).
 * Faz 1.0a: Guzzle'a taşınır; bu sınıf interface'i korur.
 */
final class EvdsClient
{
    private string $baseUrl;
    private ?string $apiKey;
    private bool $mockMode;
    private int $timeoutSeconds;

    public function __construct(
        ?string $apiKey = null,
        bool $mockMode = true,
        string $baseUrl = 'https://evds2.tcmb.gov.tr/service/evds',
        int $timeoutSeconds = 15
    ) {
        $this->apiKey         = $apiKey;
        $this->mockMode       = $mockMode || $apiKey === null || $apiKey === '';
        $this->baseUrl        = rtrim($baseUrl, '/');
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function isMockMode(): bool
    {
        return $this->mockMode;
    }

    /**
     * Bir endeks serisinin aylık değerlerini çeker.
     *
     * @return array<string, float>  YYYY-MM => value
     */
    public function fetchMonthlySeries(string $evdsCode, string $startMonth, string $endMonth): array
    {
        if ($this->mockMode) {
            return $this->mockSeries($evdsCode, $startMonth, $endMonth);
        }

        if (!preg_match('/^(\d{4})-(\d{2})$/', $startMonth) || !preg_match('/^(\d{4})-(\d{2})$/', $endMonth)) {
            throw new RuntimeException('Geçersiz tarih formatı; YYYY-MM bekleniyor.');
        }

        $sd = date('d-m-Y', strtotime($startMonth . '-01'));
        $ed = date('d-m-Y', strtotime($endMonth . '-01'));

        $url = sprintf(
            '%s/series=%s&startDate=%s&endDate=%s&type=json&key=%s&aggregationTypes=avg&frequency=5',
            $this->baseUrl,
            urlencode($evdsCode),
            $sd,
            $ed,
            urlencode($this->apiKey ?? '')
        );

        $ctx = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => $this->timeoutSeconds,
                'header'  => "Accept: application/json\r\nUser-Agent: Yemekhaneci-EVDS-Client/0.5\r\n",
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $ctx);
        if ($body === false) {
            throw new RuntimeException("EVDS isteği başarısız: {$evdsCode}");
        }

        $data = json_decode($body, true);
        if (!is_array($data) || !isset($data['items'])) {
            throw new RuntimeException("EVDS yanıtı çözümlenemedi: {$evdsCode}");
        }

        $out = [];
        foreach ($data['items'] as $item) {
            $tarih = $item['Tarih'] ?? null;
            $value = $item[$this->normalizeColumn($evdsCode)] ?? null;
            if ($tarih === null || $value === null) continue;
            // Tarih biçimi: "01-2025" veya "01.01.2025"
            if (preg_match('/^(\d{2})-(\d{4})$/', (string) $tarih, $m)) {
                $key = sprintf('%04d-%02d', (int) $m[2], (int) $m[1]);
            } elseif (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', (string) $tarih, $m)) {
                $key = sprintf('%04d-%02d', (int) $m[3], (int) $m[2]);
            } else {
                continue;
            }
            $out[$key] = (float) $value;
        }
        ksort($out);
        return $out;
    }

    /**
     * EVDS yanıtında kolon adı endeks kodu ile eşleşmez (örn. "TP_FG_J0").
     */
    private function normalizeColumn(string $code): string
    {
        return str_replace('.', '_', $code);
    }

    /**
     * Mock veri üretici — gerçek TÜFE/Yİ-ÜFE benzeri sentetik aylık seri.
     *
     * @return array<string, float>
     */
    private function mockSeries(string $evdsCode, string $startMonth, string $endMonth): array
    {
        // Endeks koduna göre sentetik bileşik aylık artış oranı.
        $config = match ($evdsCode) {
            'TP.FG.J0'    => ['start' => 580.0, 'rate' => 2.65],   // TÜFE genel
            'TP.FG.J01'   => ['start' => 670.0, 'rate' => 3.05],   // TÜFE gıda
            'TP.FE.OKTG01'=> ['start' => 510.0, 'rate' => 2.35],   // Yİ-ÜFE
            default       => ['start' => 100.0, 'rate' => 2.50],
        };

        [$sy, $sm] = array_map('intval', explode('-', $startMonth));
        [$ey, $em] = array_map('intval', explode('-', $endMonth));

        // Mock baz tarihi: 2020-01
        $baseY = 2020;
        $baseM = 1;
        $monthsFromBase = function (int $y, int $m) use ($baseY, $baseM): int {
            return ($y - $baseY) * 12 + ($m - $baseM);
        };

        $out = [];
        $year = $sy; $month = $sm;
        while ($year < $ey || ($year === $ey && $month <= $em)) {
            $offset = $monthsFromBase($year, $month);
            $value = $config['start'] * pow(1 + $config['rate'] / 100, max(0, $offset));
            $key = sprintf('%04d-%02d', $year, $month);
            $out[$key] = round($value, 4);
            $month++;
            if ($month > 12) { $month = 1; $year++; }
        }

        return $out;
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class InflationApiTest extends TestCase
{
    private HttpClient $http;

    protected function setUp(): void
    {
        // Her test için temiz rate limiter (testler arasında 429 sızmasın)
        $rlDir = dirname(__DIR__, 2) . '/storage/ratelimit';
        if (is_dir($rlDir)) {
            foreach (glob($rlDir . '/*.txt') ?: [] as $f) @unlink($f);
        }

        $this->http = new HttpClient(TestServer::url());
        // Sayfayı çağır, CSRF + session cookie al
        $this->http->get('/araclar/enflasyon-hesaplayici');
    }

    public function test_gecerli_hesaplama(): void
    {
        $r = $this->http->post('/api/v1/enflasyon/hesapla', [
            'source'      => 'tuik_tufe_gida',
            'start_date'  => '2024-03',
            'end_date'    => '2026-05',
            'start_price' => '5000',
            'panel_origin'=> 'public',
        ]);
        $this->assertSame(200, $r->statusCode);
        $this->assertTrue($r->isJson());
        $data = $r->json();
        $this->assertTrue($data['success'] ?? false);
        $this->assertGreaterThan(5000, $data['data']['end_price']);
        $this->assertGreaterThan(0, $data['data']['change_pct']);
    }

    public function test_csrf_olmadan_419(): void
    {
        $client = new HttpClient(TestServer::url());
        // Hiç sayfa çağrılmadığı için CSRF yok
        $r = $client->post('/api/v1/enflasyon/hesapla', [
            '_csrf' => 'invalid',
            'source' => 'tuik_tufe', 'start_date' => '2024-01',
            'end_date' => '2026-01', 'start_price' => '1000',
        ]);
        $this->assertSame(419, $r->statusCode);
        $this->assertSame('CSRF', $r->json()['error']);
    }

    public function test_eksik_alanlar_422(): void
    {
        $r = $this->http->post('/api/v1/enflasyon/hesapla', []);
        $this->assertSame(422, $r->statusCode);
        $errors = $r->json()['errors'] ?? [];
        $this->assertArrayHasKey('source', $errors);
        $this->assertArrayHasKey('start_date', $errors);
        $this->assertArrayHasKey('start_price', $errors);
    }

    public function test_lead_capture_kvkk_zorunlu(): void
    {
        $r = $this->http->post('/api/v1/enflasyon/mail-gonder', [
            'email'      => 'test@example.com',
            'source'     => 'tuik_tufe',
            'start_date' => '2024-01',
            'end_date'   => '2026-04',
            'start_price'=> '1000',
            // kvkk yok!
        ]);
        $this->assertSame(422, $r->statusCode);
        $this->assertArrayHasKey('kvkk', $r->json()['errors'] ?? []);
    }

    public function test_lead_capture_basarili(): void
    {
        $r = $this->http->post('/api/v1/enflasyon/mail-gonder', [
            'email'      => 'feature_test@example.com',
            'kvkk'       => '1',
            'source'     => 'tuik_tufe',
            'start_date' => '2024-01',
            'end_date'   => '2026-04',
            'start_price'=> '1000',
            'panel_origin'=>'public',
        ]);
        $this->assertSame(200, $r->statusCode);
        $this->assertTrue($r->json()['success']);
    }

    public function test_rate_limit_30_dakika(): void
    {
        // Rate limit calc için 30/dk; 32 istek atınca en az birkaç 429 gelmeli
        $codes = [];
        for ($i = 0; $i < 35; $i++) {
            $r = $this->http->post('/api/v1/enflasyon/hesapla', [
                'source' => 'tuik_tufe', 'start_date' => '2024-01',
                'end_date' => '2026-01', 'start_price' => '100',
            ]);
            $codes[] = $r->statusCode;
        }
        $this->assertContains(429, $codes, 'Rate limit 429 üretmeli');
        $this->assertContains(200, array_slice($codes, 0, 25), 'İlk 25 istek 200 olmalı');
    }
}

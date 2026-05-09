<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class QuickQuoteTest extends TestCase
{
    private HttpClient $http;

    protected function setUp(): void
    {
        $this->http = new HttpClient(TestServer::url());
        $this->http->get('/');
    }

    private function validPayload(): array
    {
        return [
            'guest_count'           => 250,
            'meals[ogle]'           => 200,
            'meals[aksam]'          => 50,
            'meals[kumanya]'        => 0,
            'menu[soup]'            => '1',
            'menu[main_dish]'       => '1',
            'menu[side_dish]'       => '1',
            'menu[bread]'           => '1',
            'menu[salad_bar_count]' => 2,
            'menu[dessert]'         => 'fruit',
            'menu[drinks]'          => 'rotation',
            'segment'               => 'genel',
            'location[city]'        => 'İstanbul',
            'location[district]'    => 'Şişli',
            'saturday'              => 'no',
            'contact_email'         => 'test@firma.com.tr',
            'contact_phone'         => '0532 123 45 67',
            'kvkk'                  => '1',
        ];
    }

    public function test_gecerli_9_soru_talebi(): void
    {
        $r = $this->http->post('/api/v1/hizli-teklif', $this->validPayload());
        $this->assertSame(200, $r->statusCode);
        $data = $r->json();
        $this->assertTrue($data['success']);
        $this->assertMatchesRegularExpression('/^YHC-\d{4}-[A-F0-9]{4}$/', $data['data']['reference']);
    }

    public function test_iletisim_eksik_422(): void
    {
        $payload = $this->validPayload();
        unset($payload['contact_email'], $payload['contact_phone']);
        $r = $this->http->post('/api/v1/hizli-teklif', $payload);
        $this->assertSame(422, $r->statusCode);
        $this->assertArrayHasKey('contact', $r->json()['errors'] ?? []);
    }

    public function test_kvkk_zorunlu(): void
    {
        $payload = $this->validPayload();
        unset($payload['kvkk']);
        $r = $this->http->post('/api/v1/hizli-teklif', $payload);
        $this->assertSame(422, $r->statusCode);
        $this->assertArrayHasKey('kvkk', $r->json()['errors'] ?? []);
    }

    public function test_ogun_secimi_zorunlu(): void
    {
        $payload = $this->validPayload();
        $payload['meals[ogle]']    = 0;
        $payload['meals[aksam]']   = 0;
        $payload['meals[kumanya]'] = 0;
        $r = $this->http->post('/api/v1/hizli-teklif', $payload);
        $this->assertSame(422, $r->statusCode);
        $this->assertArrayHasKey('meals', $r->json()['errors'] ?? []);
    }

    public function test_gecersiz_segment_422(): void
    {
        $payload = $this->validPayload();
        $payload['segment'] = 'altin';
        $r = $this->http->post('/api/v1/hizli-teklif', $payload);
        $this->assertSame(422, $r->statusCode);
    }

    public function test_sehir_eksik_422(): void
    {
        $payload = $this->validPayload();
        $payload['location[city]'] = '';
        $r = $this->http->post('/api/v1/hizli-teklif', $payload);
        $this->assertSame(422, $r->statusCode);
        $this->assertArrayHasKey('city', $r->json()['errors'] ?? []);
    }
}

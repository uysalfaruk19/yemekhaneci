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

    public function test_gecerli_talep(): void
    {
        $r = $this->http->post('/api/v1/hizli-teklif', [
            'guest_count'  => 250,
            'meal_type'    => 'aksam',
            'event_date'   => date('Y-m-d', strtotime('+30 days')),
            'city'         => 'İstanbul',
            'district'     => 'Şişli',
            'contact_email'=> 'test@firma.com.tr',
            'contact_phone'=> '0532 123 45 67',
            'kvkk'         => '1',
        ]);
        $this->assertSame(200, $r->statusCode);
        $data = $r->json();
        $this->assertTrue($data['success']);
        $this->assertMatchesRegularExpression('/^YHC-\d{4}-[A-F0-9]{4}$/', $data['data']['reference']);
    }

    public function test_iletisim_eksik_422(): void
    {
        $r = $this->http->post('/api/v1/hizli-teklif', [
            'guest_count' => 100, 'meal_type' => 'ogle',
            'event_date'  => date('Y-m-d', strtotime('+10 days')),
            'city'        => 'Ankara', 'kvkk' => '1',
            // contact_email & contact_phone yok
        ]);
        $this->assertSame(422, $r->statusCode);
        $this->assertArrayHasKey('contact', $r->json()['errors'] ?? []);
    }

    public function test_kvkk_zorunlu(): void
    {
        $r = $this->http->post('/api/v1/hizli-teklif', [
            'guest_count' => 100, 'meal_type' => 'ogle',
            'event_date'  => date('Y-m-d', strtotime('+10 days')),
            'city'        => 'Ankara',
            'contact_email' => 't@t.com',
            // kvkk yok
        ]);
        $this->assertSame(422, $r->statusCode);
        $this->assertArrayHasKey('kvkk', $r->json()['errors'] ?? []);
    }

    public function test_gecmis_tarih_422(): void
    {
        $r = $this->http->post('/api/v1/hizli-teklif', [
            'guest_count'  => 100, 'meal_type' => 'ogle',
            'event_date'   => '2020-01-01',
            'city'         => 'Ankara', 'contact_email' => 't@t.com', 'kvkk' => '1',
        ]);
        $this->assertSame(422, $r->statusCode);
        $errors = $r->json()['errors'] ?? [];
        $this->assertArrayHasKey('event_date', $errors);
        $this->assertStringContainsString('bugün', $errors['event_date'][0]);
    }

    public function test_gecersiz_meal_type_422(): void
    {
        $r = $this->http->post('/api/v1/hizli-teklif', [
            'guest_count' => 100, 'meal_type' => 'kahvalti',
            'event_date'  => date('Y-m-d', strtotime('+10 days')),
            'city' => 'A', 'contact_email' => 't@t.com', 'kvkk' => '1',
        ]);
        $this->assertSame(422, $r->statusCode);
    }
}

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
            'meal_count'              => 2,
            'guest_count'             => 250,
            'menu[soup]'              => '1',
            'menu[main_dish]'         => '1',
            'menu[side_dish]'         => '1',
            'menu[bread]'             => '1',
            'menu[salad_bar_count]'   => 3,
            'menu[dessert_rotation]'  => '1',
            'menu[drinks]'            => 'rotation',
            'segment'                 => 'genel',
            'location[city]'          => 'İstanbul',
            'location[district]'      => 'Şişli',
            'equipment[has_existing]' => 'Endüstriyel ocak, bulaşık makinesi',
            'equipment[requested]'    => 'Sanayi fırın',
            'saturday'                => 'no',
        ];
    }

    public function test_gecerli_talep_fiyat_doner(): void
    {
        $r = $this->http->post('/api/v1/hizli-teklif', $this->validPayload());
        $this->assertSame(200, $r->statusCode);
        $data = $r->json();
        $this->assertTrue($data['success']);
        $this->assertMatchesRegularExpression('/^YHC-\d{4}-[A-F0-9]{4}$/', $data['data']['reference']);
        $this->assertGreaterThan(100, $data['data']['pricing']['per_person_per_meal']);
        $this->assertCount(5, $data['data']['anonymous_suppliers']);
    }

    public function test_meal_count_zorunlu(): void
    {
        $payload = $this->validPayload();
        $payload['meal_count'] = 0;
        $r = $this->http->post('/api/v1/hizli-teklif', $payload);
        $this->assertSame(422, $r->statusCode);
        $this->assertArrayHasKey('meal_count', $r->json()['errors'] ?? []);
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

    public function test_iletisim_endpoint_kvkk_zorunlu(): void
    {
        // Önce talep oluştur
        $r = $this->http->post('/api/v1/hizli-teklif', $this->validPayload());
        $reference = $r->json()['data']['reference'];

        // KVKK olmadan iletişim ekleme
        $r2 = $this->http->post('/api/v1/hizli-teklif/iletisim', [
            'reference'     => $reference,
            'contact_email' => 'test@firma.com',
        ]);
        $this->assertSame(422, $r2->statusCode);
        $this->assertArrayHasKey('kvkk', $r2->json()['errors'] ?? []);
    }

    public function test_iletisim_endpoint_basarili(): void
    {
        $r = $this->http->post('/api/v1/hizli-teklif', $this->validPayload());
        $reference = $r->json()['data']['reference'];

        $r2 = $this->http->post('/api/v1/hizli-teklif/iletisim', [
            'reference'     => $reference,
            'contact_email' => 'test@firma.com',
            'contact_phone' => '0532 111 22 33',
            'kvkk'          => '1',
        ]);
        $this->assertSame(200, $r2->statusCode);
        $this->assertTrue($r2->json()['success']);
    }

    public function test_iletisim_olmayan_referans_404(): void
    {
        $r = $this->http->post('/api/v1/hizli-teklif/iletisim', [
            'reference'     => 'YHC-9999-DEAD',     // hex format doğru ama kayıt yok
            'contact_email' => 't@t.com', 'kvkk' => '1',
        ]);
        $this->assertSame(404, $r->statusCode);
    }
}

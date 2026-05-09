<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class PublicRoutesTest extends TestCase
{
    private HttpClient $http;

    protected function setUp(): void
    {
        $this->http = new HttpClient(TestServer::url());
    }

    public function test_anasayfa_200(): void
    {
        $r = $this->http->get('/');
        $this->assertSame(200, $r->statusCode);
        $this->assertStringContainsString('Yemekhaneci', $r->body);
        $this->assertStringContainsString('Hızlı Teklif', $r->body);
    }

    public function test_enflasyon_hesaplayici_200(): void
    {
        $r = $this->http->get('/araclar/enflasyon-hesaplayici');
        $this->assertSame(200, $r->statusCode);
        $this->assertStringContainsString('Enflasyon', $r->body);
    }

    public function test_yasal_sayfalar_200(): void
    {
        foreach (['/yasal/aydinlatma-metni', '/yasal/cerez-politikasi', '/yasal/kullanim-kosullari', '/yasal/veri-silme'] as $path) {
            $r = $this->http->get($path);
            $this->assertSame(200, $r->statusCode, "{$path} 200 dönmeli");
        }
    }

    public function test_404_bilinmeyen_sayfa(): void
    {
        $r = $this->http->get('/bunlar-olmayan-bir-sayfa');
        $this->assertSame(404, $r->statusCode);
        $this->assertStringContainsString('404', $r->body);
    }

    public function test_guvenlik_headerlari(): void
    {
        $r = $this->http->get('/');
        $this->assertArrayHasKey('X-Content-Type-Options', $r->headers);
        $this->assertSame('nosniff', $r->headers['X-Content-Type-Options']);
        $this->assertSame('SAMEORIGIN', $r->headers['X-Frame-Options']);
        $this->assertArrayHasKey('Referrer-Policy', $r->headers);
        $this->assertArrayHasKey('Content-Security-Policy-Report-Only', $r->headers);
    }

    public function test_cerez_banner_anasayfada(): void
    {
        $r = $this->http->get('/');
        $this->assertStringContainsString('cookieConsent', $r->body);
        $this->assertStringContainsString('Çerez kullanıyoruz', $r->body);
    }

    public function test_footer_yasal_linkleri(): void
    {
        $r = $this->http->get('/');
        $this->assertStringContainsString('/yasal/aydinlatma-metni', $r->body);
        $this->assertStringContainsString('/yasal/cerez-politikasi', $r->body);
    }
}

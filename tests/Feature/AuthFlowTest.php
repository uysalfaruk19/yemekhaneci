<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class AuthFlowTest extends TestCase
{
    private HttpClient $http;

    protected function setUp(): void
    {
        $this->http = new HttpClient(TestServer::url());
    }

    public function test_login_sayfasi_200(): void
    {
        $r = $this->http->get('/giris-yap');
        $this->assertSame(200, $r->statusCode);
        $this->assertStringContainsString('Giriş Yap', $r->body);
        $this->assertNotNull($this->http->getCsrf(), 'Sayfada CSRF token olmalı');
    }

    public function test_yanlis_sifre_redirect_with_error(): void
    {
        $this->http->get('/giris-yap');                  // CSRF al
        $r = $this->http->post('/giris-yap', ['username' => 'OFU', 'password' => 'yanlis']);
        $this->assertSame(302, $r->statusCode);
        $this->assertStringContainsString('/giris-yap', $r->location ?? '');
    }

    public function test_dogru_sifre_admin_panele_yonlendirir(): void
    {
        $this->http->get('/giris-yap');
        $r = $this->http->post('/giris-yap', ['username' => 'OFU', 'password' => '1234']);
        $this->assertSame(302, $r->statusCode);
        $this->assertStringContainsString('/yonetim', $r->location ?? '');

        // Admin paneli artık erişilebilir
        $admin = $this->http->get('/yonetim');
        $this->assertSame(200, $admin->statusCode);
        $this->assertStringContainsString('Ömer M.', $admin->body);
    }

    public function test_yemekci_uysa_kendi_paneline_erisir(): void
    {
        $this->http->get('/giris-yap');
        $r = $this->http->post('/giris-yap', ['username' => 'Uysa', 'password' => '1234']);
        $this->assertSame(302, $r->statusCode);
        $this->assertStringContainsString('/yemekci', $r->location ?? '');

        $supplier = $this->http->get('/yemekci');
        $this->assertSame(200, $supplier->statusCode);
        $this->assertStringContainsString('UYSA Yemek', $supplier->body);
    }

    public function test_yemekci_admin_paneline_erisemez_403(): void
    {
        $this->http->get('/giris-yap');
        $this->http->post('/giris-yap', ['username' => 'Uysa', 'password' => '1234']);
        $r = $this->http->get('/yonetim');
        $this->assertSame(403, $r->statusCode);
        $this->assertStringContainsString('Yetkisiz', $r->body);
    }

    public function test_anonim_kullanici_yemekciye_giderse_login_a_redirect(): void
    {
        $r = $this->http->get('/yemekci');
        $this->assertSame(302, $r->statusCode);
        $this->assertStringContainsString('/giris-yap', $r->location ?? '');
    }

    public function test_csrf_korumasiz_post_cikis_yap_yonlenir(): void
    {
        // Cikis yap CSRF olmadan → ana sayfaya redirect (saldırı koruması)
        $r = $this->http->post('/cikis-yap', ['_csrf' => 'invalid-token-12345']);
        $this->assertSame(302, $r->statusCode);
    }

    public function test_logout_basarili(): void
    {
        $this->http->get('/giris-yap');
        $this->http->post('/giris-yap', ['username' => 'OFU', 'password' => '1234']);
        $this->http->get('/yonetim');                    // CSRF için
        $r = $this->http->post('/cikis-yap', []);
        $this->assertSame(302, $r->statusCode);
        $this->assertStringContainsString('/giris-yap', $r->location ?? '');

        // Çıktıktan sonra /yonetim'e tekrar girmeye çalış → 302
        $r2 = $this->http->get('/yonetim');
        $this->assertSame(302, $r2->statusCode);
    }
}

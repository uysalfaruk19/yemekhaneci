<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

final class HealthCheckTest extends TestCase
{
    private HttpClient $http;

    protected function setUp(): void
    {
        $this->http = new HttpClient(TestServer::url());
    }

    public function test_saglik_liveness(): void
    {
        $r = $this->http->get('/saglik');
        $this->assertSame(200, $r->statusCode);
        $this->assertTrue($r->isJson());
        $data = $r->json();
        $this->assertSame('ok', $data['status']);
        $this->assertArrayHasKey('timestamp', $data);
    }

    public function test_hazir_readiness(): void
    {
        $r = $this->http->get('/hazir');
        $this->assertSame(200, $r->statusCode);
        $data = $r->json();
        $this->assertSame('ready', $data['status']);
        $this->assertArrayHasKey('checks', $data);
        $this->assertTrue($data['checks']['php_version']['ok']);
        $this->assertTrue($data['checks']['composer_autoload']['ok']);
        $this->assertTrue($data['checks']['storage_writable']['ok']);
        $this->assertTrue($data['checks']['config_present']['ok']);
    }

    public function test_health_endpointleri_cache_lenmez(): void
    {
        $r = $this->http->get('/saglik');
        $this->assertStringContainsString('no-store', $r->headers['Cache-Control'] ?? '');
    }
}

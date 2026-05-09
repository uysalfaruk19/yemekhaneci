<?php

declare(strict_types=1);

namespace Tests\Feature;

use RuntimeException;

/**
 * Feature testleri için minimal HTTP istemcisi.
 * Cookie jar'ı bellek içinde tutar; CSRF token'ı otomatik çıkarır.
 *
 * Kullanım:
 *   $http = new HttpClient('http://127.0.0.1:8888');
 *   $r = $http->get('/giris-yap');
 *   $http->post('/giris-yap', ['username'=>'OFU','password'=>'1234']); // CSRF auto
 */
final class HttpClient
{
    private string $baseUrl;
    /** @var array<string,string> */
    private array $cookies = [];
    private ?string $csrfToken = null;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function get(string $path, array $query = []): HttpResponse
    {
        $url = $this->baseUrl . $path . ($query ? '?' . http_build_query($query) : '');
        return $this->request('GET', $url);
    }

    /**
     * POST. Eğer `$body['_csrf']` yoksa son aldığımız token otomatik eklenir.
     */
    public function post(string $path, array $body = [], array $headers = []): HttpResponse
    {
        if (!isset($body['_csrf']) && $this->csrfToken !== null) {
            $body['_csrf'] = $this->csrfToken;
        }
        return $this->request(
            'POST',
            $this->baseUrl . $path,
            http_build_query($body),
            array_merge(['Content-Type: application/x-www-form-urlencoded'], $headers)
        );
    }

    public function postJson(string $path, array $body): HttpResponse
    {
        return $this->request(
            'POST',
            $this->baseUrl . $path,
            json_encode($body) ?: '{}',
            ['Content-Type: application/json', 'Accept: application/json']
        );
    }

    public function getCsrf(): ?string
    {
        return $this->csrfToken;
    }

    public function clearCookies(): void
    {
        $this->cookies = [];
        $this->csrfToken = null;
    }

    private function request(string $method, string $url, ?string $body = null, array $headers = []): HttpResponse
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => $this->mergeHeaders($headers),
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        if ($response === false) {
            throw new RuntimeException('curl error: ' . curl_error($ch));
        }
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $rawHeaders = (string) substr((string) $response, 0, $headerSize);
        $body = (string) substr((string) $response, $headerSize);
        curl_close($ch);

        $parsed = $this->parseHeaders($rawHeaders);
        $this->absorbSetCookie($parsed['set_cookie'] ?? []);

        // CSRF token'ı body'den çıkar (ilk eşleşme — form _csrf hidden veya inflationApp csrf)
        if (preg_match('/name="_csrf"\s+value="([^"]+)"/', $body, $m)) {
            $this->csrfToken = $m[1];
        } elseif (preg_match("/csrf:\s*'([^']+)'/", $body, $m)) {
            $this->csrfToken = $m[1];
        }

        return new HttpResponse(
            statusCode: $statusCode,
            headers: $parsed['headers'],
            body: $body,
            location: $parsed['headers']['Location'] ?? null
        );
    }

    /** @return array<int,string> */
    private function mergeHeaders(array $extra): array
    {
        $base = [];
        if ($this->cookies) {
            $base[] = 'Cookie: ' . http_build_query($this->cookies, '', '; ', PHP_QUERY_RFC3986);
        }
        return array_merge($base, $extra);
    }

    /**
     * @return array{headers:array<string,string>, set_cookie:array<int,string>}
     */
    private function parseHeaders(string $raw): array
    {
        $headers = [];
        $setCookie = [];
        foreach (preg_split('/\r?\n/', trim($raw)) ?: [] as $line) {
            if (strpos($line, ':') === false) continue;
            [$k, $v] = array_map('trim', explode(':', $line, 2));
            if (strcasecmp($k, 'Set-Cookie') === 0) {
                $setCookie[] = $v;
            } else {
                $headers[$k] = $v;
            }
        }
        return ['headers' => $headers, 'set_cookie' => $setCookie];
    }

    /** @param array<int,string> $setCookies */
    private function absorbSetCookie(array $setCookies): void
    {
        foreach ($setCookies as $line) {
            $parts = explode(';', $line);
            $kv = explode('=', $parts[0], 2);
            if (count($kv) !== 2) continue;
            $name = trim($kv[0]);
            $value = trim($kv[1]);
            // 'expires=...' veya 'Max-Age=0' kontrolü ile silme algıla
            if ($value === '' || $value === 'deleted') {
                unset($this->cookies[$name]);
            } else {
                $this->cookies[$name] = $value;
            }
        }
    }
}

final class HttpResponse
{
    public function __construct(
        public readonly int $statusCode,
        /** @var array<string,string> */
        public readonly array $headers,
        public readonly string $body,
        public readonly ?string $location = null,
    ) {}

    public function isJson(): bool
    {
        return str_contains(strtolower($this->headers['Content-Type'] ?? ''), 'application/json');
    }

    public function json(): array
    {
        $decoded = json_decode($this->body, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function contains(string $needle): bool
    {
        return str_contains($this->body, $needle);
    }
}

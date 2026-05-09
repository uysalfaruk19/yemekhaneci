<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\TotpService;
use PHPUnit\Framework\TestCase;

final class TotpServiceTest extends TestCase
{
    public function test_generate_secret_base32_uzunluk(): void
    {
        $secret = TotpService::generateSecret(32);
        $this->assertSame(32, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function test_base32_encode_decode_roundtrip(): void
    {
        $original = random_bytes(20);
        $encoded = TotpService::base32Encode($original);
        $decoded = TotpService::base32Decode($encoded);
        $this->assertSame($original, $decoded);
    }

    public function test_token_6_hane(): void
    {
        $secret = TotpService::generateSecret();
        $token = TotpService::generateToken($secret, 1);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $token);
    }

    public function test_verify_dogru_token(): void
    {
        $secret = TotpService::generateSecret();
        $slice = intdiv(time(), 30);
        $token = TotpService::generateToken($secret, $slice);
        $this->assertTrue(TotpService::verify($secret, $token));
    }

    public function test_verify_yanlis_token(): void
    {
        $secret = TotpService::generateSecret();
        $this->assertFalse(TotpService::verify($secret, '000000'));
        $this->assertFalse(TotpService::verify($secret, 'abc'));      // formatsiz
        $this->assertFalse(TotpService::verify($secret, '12345'));    // 5 hane
    }

    public function test_verify_skew_tolerance(): void
    {
        $secret = TotpService::generateSecret();
        $slice = intdiv(time(), 30);
        // 1 period öncesi, 1 period sonrası kabul edilmeli (discrepancy=1)
        $tokenPast   = TotpService::generateToken($secret, $slice - 1);
        $tokenFuture = TotpService::generateToken($secret, $slice + 1);
        $this->assertTrue(TotpService::verify($secret, $tokenPast));
        $this->assertTrue(TotpService::verify($secret, $tokenFuture));

        // 2 period uzakta (discrepancy=1) reddedilmeli
        $tokenFar = TotpService::generateToken($secret, $slice + 2);
        $this->assertFalse(TotpService::verify($secret, $tokenFar, 1));
    }

    public function test_provisioning_uri(): void
    {
        $secret = TotpService::generateSecret();
        $uri = TotpService::provisioningUri($secret, 'omer@uysa.com.tr', 'Yemekhaneci');
        $this->assertStringStartsWith('otpauth://totp/', $uri);
        $this->assertStringContainsString('secret=' . $secret, $uri);
        $this->assertStringContainsString('issuer=Yemekhaneci', $uri);
        $this->assertStringContainsString('digits=6', $uri);
        $this->assertStringContainsString('period=30', $uri);
    }

    public function test_qr_code_url(): void
    {
        $uri = TotpService::provisioningUri('JBSWY3DPEHPK3PXP', 'test');
        $qrUrl = TotpService::qrCodeImageUrl($uri);
        $this->assertStringContainsString('qrserver.com', $qrUrl);
        $this->assertStringContainsString(rawurlencode($uri), $qrUrl);
    }

    public function test_rfc6238_referans_vektorler(): void
    {
        // RFC 6238 Appendix B test vectors (SHA1, 8 digit) — biz 6 digit kullandığımız için
        // sadece secret & generation tutarlılığını doğruluyoruz. Resmî 6 digit vector:
        // Time = 59     T = 0x0000000000000001  Token = 287082
        // Time = 1111111109  T = 0x00000000023523EC  Token = 081804

        // RFC vectorleri 20-byte ASCII secret kullanır: "12345678901234567890"
        $rfcSecret = TotpService::base32Encode('12345678901234567890');

        $this->assertSame('287082', TotpService::generateToken($rfcSecret, 1));
        $this->assertSame('081804', TotpService::generateToken($rfcSecret, 0x023523EC));
    }
}

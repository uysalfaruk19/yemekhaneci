<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

/**
 * TOTP (RFC 6238) servisi — Google Authenticator / Authy uyumlu.
 *
 * Faz 0.5 demo için minimal implementasyon. Faz 1.0a'da
 * `composer require robthree/twofactorauth` ile gelişmiş kütüphaneye geçilebilir
 * (clock skew detection, QR code üretimi, vb.).
 */
final class TotpService
{
    private const PERIOD_SECONDS = 30;
    private const DIGITS = 6;
    private const ALGORITHM = 'sha1';

    /**
     * Yeni rastgele secret üret (160 bit, base32 kodlu).
     */
    public static function generateSecret(int $length = 32): string
    {
        $bytes = random_bytes((int) ceil($length * 5 / 8));
        return substr(self::base32Encode($bytes), 0, $length);
    }

    /**
     * Mevcut TOTP token'ı verili secret için doğrular.
     * `$discrepancy` ile saat farkı toleransı (her yön için ±N period).
     */
    public static function verify(string $secret, string $token, int $discrepancy = 1): bool
    {
        $token = preg_replace('/\s+/', '', $token) ?? $token;
        if (!preg_match('/^\d{' . self::DIGITS . '}$/', $token)) return false;

        $currentSlice = intdiv(time(), self::PERIOD_SECONDS);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $expected = self::generateToken($secret, $currentSlice + $i);
            if (hash_equals($expected, $token)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Belirli bir zaman dilimi için token üret (ihtiyaç durumunda dışa açık).
     */
    public static function generateToken(string $secret, int $timeSlice): string
    {
        $key = self::base32Decode($secret);
        if ($key === '') {
            throw new InvalidArgumentException('Secret base32 decode başarısız.');
        }

        $time = pack('N*', 0, $timeSlice);
        $hash = hash_hmac(self::ALGORITHM, $time, $key, true);

        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $code = (
            ((ord($hash[$offset])     & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) <<  8) |
             (ord($hash[$offset + 3]) & 0xFF)
        ) % (10 ** self::DIGITS);

        return str_pad((string) $code, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * otpauth:// URI üret — QR code generator'a verilebilir.
     * format: otpauth://totp/{issuer}:{label}?secret=...&issuer=...&algorithm=SHA1&digits=6&period=30
     */
    public static function provisioningUri(string $secret, string $accountName, string $issuer = 'Yemekhaneci'): string
    {
        $params = http_build_query([
            'secret'    => $secret,
            'issuer'    => $issuer,
            'algorithm' => 'SHA1',
            'digits'    => self::DIGITS,
            'period'    => self::PERIOD_SECONDS,
        ]);
        $label = rawurlencode($issuer . ':' . $accountName);
        return "otpauth://totp/{$label}?{$params}";
    }

    /**
     * QR code görseli için Google Charts API URL'si (alternatif: client-side `qrcodejs`).
     */
    public static function qrCodeImageUrl(string $provisioningUri, int $size = 200): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size
            . '&data=' . rawurlencode($provisioningUri);
    }

    // ---- base32 encode/decode (RFC 4648) ----

    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function base32Encode(string $bytes): string
    {
        if ($bytes === '') return '';
        $binary = '';
        foreach (str_split($bytes) as $byte) {
            $binary .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }
        $binary = str_pad($binary, (int) (ceil(strlen($binary) / 5) * 5), '0', STR_PAD_RIGHT);

        $out = '';
        foreach (str_split($binary, 5) as $chunk) {
            $out .= self::BASE32_ALPHABET[bindec($chunk)];
        }
        return $out;
    }

    public static function base32Decode(string $encoded): string
    {
        $encoded = strtoupper(rtrim($encoded, '='));
        if ($encoded === '') return '';

        $binary = '';
        foreach (str_split($encoded) as $char) {
            $idx = strpos(self::BASE32_ALPHABET, $char);
            if ($idx === false) return '';
            $binary .= str_pad(decbin($idx), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($binary, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }
        return $bytes;
    }
}

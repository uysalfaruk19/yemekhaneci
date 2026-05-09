<?php

declare(strict_types=1);

namespace App\Repositories;

use RuntimeException;

/**
 * 2FA secret'ları + recovery code'ları (Faz 0.5).
 * Faz 1.0a'da `users.totp_secret` (encrypted at rest) sütununa taşınır.
 *
 * Storage: storage/auth/totp.json (gitignored)
 *   { "OFU": { "secret": "...", "enabled_at": "2026-...", "recovery_codes": [...] } }
 */
final class TotpSecretRepository
{
    private string $dataFile;

    public function __construct(?string $dataFile = null)
    {
        $this->dataFile = $dataFile ?? \app_path('storage/auth/totp.json');
        $dir = dirname($this->dataFile);
        if (!is_dir($dir)) mkdir($dir, 0750, true);
        if (!file_exists($this->dataFile)) {
            file_put_contents($this->dataFile, '{}');
        }
    }

    public function isEnabled(string $username): bool
    {
        $rec = $this->find($username);
        return $rec !== null && !empty($rec['enabled_at']);
    }

    public function find(string $username): ?array
    {
        $store = $this->readAll();
        return $store[$username] ?? null;
    }

    public function getSecret(string $username): ?string
    {
        return $this->find($username)['secret'] ?? null;
    }

    /**
     * Setup başlat — secret üretip kaydet, ama henüz enable etme.
     * Kullanıcı QR scan + ilk doğrulama sonrası enable() çağrılır.
     */
    public function startSetup(string $username, string $secret): void
    {
        $this->mutate(static function (array &$store) use ($username, $secret): void {
            $store[$username] = [
                'secret'         => $secret,
                'enabled_at'     => null,
                'recovery_codes' => [],
                'started_at'     => date('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * 2FA'yı aktif et + recovery code'ları üret (8 adet, 8 karakter, hashed).
     * Plaintext code'lar tek seferlik kullanıcıya gösterilir.
     *
     * @return array<int,string>  Plaintext recovery codes
     */
    public function enable(string $username): array
    {
        $codes = [];
        $hashed = [];
        for ($i = 0; $i < 8; $i++) {
            $code = strtoupper(bin2hex(random_bytes(4))); // 8 hex chars
            $codes[] = $code;
            $hashed[] = password_hash($code, PASSWORD_BCRYPT);
        }

        $this->mutate(static function (array &$store) use ($username, $hashed): void {
            if (!isset($store[$username])) {
                throw new RuntimeException("Önce startSetup çağrılmalı: {$username}");
            }
            $store[$username]['enabled_at']     = date('Y-m-d H:i:s');
            $store[$username]['recovery_codes'] = $hashed;
        });

        return $codes;
    }

    public function disable(string $username): void
    {
        $this->mutate(static function (array &$store) use ($username): void {
            unset($store[$username]);
        });
    }

    /**
     * Recovery code ile giriş — eşleşen ve KULLANILMAMIŞ code'u harca.
     * @return bool  Eşleşti mi
     */
    public function consumeRecoveryCode(string $username, string $providedCode): bool
    {
        $providedCode = strtoupper(trim($providedCode));
        $matched = false;

        $this->mutate(static function (array &$store) use ($username, $providedCode, &$matched): void {
            $rec = $store[$username] ?? null;
            if (!$rec || empty($rec['recovery_codes'])) return;

            foreach ($rec['recovery_codes'] as $idx => $hash) {
                if (password_verify($providedCode, $hash)) {
                    array_splice($store[$username]['recovery_codes'], $idx, 1);
                    $matched = true;
                    return;
                }
            }
        });

        return $matched;
    }

    public function recoveryCodesRemaining(string $username): int
    {
        $rec = $this->find($username);
        return count($rec['recovery_codes'] ?? []);
    }

    /** @return array<string, array<string,mixed>> */
    private function readAll(): array
    {
        $raw = file_get_contents($this->dataFile);
        if ($raw === false || trim($raw) === '') return [];
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /** @param callable(array<string,mixed>): void $mutator */
    private function mutate(callable $mutator): void
    {
        $fp = fopen($this->dataFile, 'c+');
        if ($fp === false) throw new RuntimeException('totp.json açılamadı.');
        try {
            flock($fp, LOCK_EX);
            $contents = stream_get_contents($fp) ?: '{}';
            $store = json_decode($contents, true) ?: [];
            $mutator($store);
            ftruncate($fp, 0); rewind($fp);
            fwrite($fp, (string) json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            fflush($fp);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}

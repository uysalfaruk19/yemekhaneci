<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Dosya tabanlı sliding-window rate limiter (Faz 0.5).
 * Faz 1.0a'da Redis tabanlı `predis/predis` ile değiştirilecek (PRD §1.3).
 *
 * Kullanım:
 *   $rl = new RateLimiter('inflation_calc', limit: 30, windowSeconds: 60);
 *   if (!$rl->allow($ipAddress)) { 429 dön; }
 */
final class RateLimiter
{
    private string $bucket;
    private int $limit;
    private int $window;
    private string $storageDir;

    public function __construct(
        string $bucket,
        int $limit = 30,
        int $windowSeconds = 60,
        ?string $storageDir = null
    ) {
        $this->bucket     = preg_replace('/[^a-z0-9_-]/i', '_', $bucket) ?? 'default';
        $this->limit      = $limit;
        $this->window     = $windowSeconds;
        $this->storageDir = $storageDir ?? \app_path('storage/ratelimit');
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Bir kovaya istek geldiğini işaretler ve limit dahilinde mi onu döndürür.
     * `allow` true dönerse kullanıcının isteği kabul edilebilir.
     */
    public function allow(string $key): bool
    {
        $hits = $this->record($key);
        return $hits <= $this->limit;
    }

    /** Anlık sayım. allow() çağırmadan önce kontrol için. */
    public function currentHits(string $key): int
    {
        $entries = $this->readEntries($key);
        return $this->countActive($entries);
    }

    /** Kalan istek hakkı. */
    public function remaining(string $key): int
    {
        return max(0, $this->limit - $this->currentHits($key));
    }

    /** Bir sonraki "boş slot"a kalan saniye (yaklaşık). */
    public function retryAfter(string $key): int
    {
        $entries = $this->readEntries($key);
        if ($entries === []) return 0;
        $oldest = min($entries);
        $delta = ($oldest + $this->window) - time();
        return max(0, $delta);
    }

    public function reset(string $key): void
    {
        $file = $this->fileFor($key);
        if (is_file($file)) @unlink($file);
    }

    // ---- iç ----

    private function record(string $key): int
    {
        $file = $this->fileFor($key);
        $fp = fopen($file, 'c+');
        if ($fp === false) return 0;
        try {
            flock($fp, LOCK_EX);
            $contents = stream_get_contents($fp) ?: '';
            $entries = $contents === '' ? [] : array_map('intval', explode(',', $contents));
            $entries = $this->filterActive($entries);
            $entries[] = time();
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, implode(',', $entries));
            fflush($fp);
            return count($entries);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /** @return int[] */
    private function readEntries(string $key): array
    {
        $file = $this->fileFor($key);
        if (!is_file($file)) return [];
        $contents = trim((string) file_get_contents($file));
        if ($contents === '') return [];
        return $this->filterActive(array_map('intval', explode(',', $contents)));
    }

    /** @param int[] $entries  @return int[] */
    private function filterActive(array $entries): array
    {
        $cutoff = time() - $this->window;
        return array_values(array_filter($entries, static fn(int $t) => $t > $cutoff));
    }

    /** @param int[] $entries */
    private function countActive(array $entries): int
    {
        return count($this->filterActive($entries));
    }

    private function fileFor(string $key): string
    {
        $safeKey = substr(hash('sha256', $key), 0, 32);
        return $this->storageDir . '/' . $this->bucket . '__' . $safeKey . '.txt';
    }
}

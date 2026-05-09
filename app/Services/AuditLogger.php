<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * Audit logger — KVKK + ticari denetim izi (Faz 0.5 P0).
 *
 * CLAUDE.md zorunlu kılıyor: tüm admin değişiklikleri kayıt edilmeli; KVKK uyumu
 * için en az 7 yıl saklanmalı. Faz 0.5'te dosya tabanlı (storage/logs/audit/);
 * Faz 1.0a'da `audit_logs` DB tablosuna taşınacak.
 *
 * Kullanım:
 *   AuditLogger::log('source.created', ['code' => 'uysa_et', 'name' => '...']);
 */
final class AuditLogger
{
    private string $logDir;
    private string $actor;

    /** Bilinen olay türleri — yeni eklerken bu listeyi güncelle (referans için). */
    public const EVENTS = [
        'auth.login_success', 'auth.login_failed', 'auth.logout',
        'source.created', 'source.updated', 'source.deleted',
        'monthly_value.added', 'monthly_value.deleted',
        'evds.triggered', 'evds.completed',
        'lead.captured',
        'quote.submitted',
        'admin.action',
    ];

    public function __construct(?string $logDir = null, string $actor = 'system')
    {
        $this->logDir = $logDir ?? \app_path('storage/logs/audit');
        $this->actor  = $actor;
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public static function for(string $actor): self
    {
        return new self(null, $actor);
    }

    /**
     * Statik kısayol — `AuditLogger::log('event', $context)`.
     * Aktör otomatik session'dan çekilir; yoksa 'anonymous'.
     */
    public static function log(string $event, array $context = [], ?string $actor = null): void
    {
        $resolvedActor = $actor ?? self::resolveActor();
        (new self(null, $resolvedActor))->record($event, $context);
    }

    public function record(string $event, array $context = []): void
    {
        $now = new \DateTimeImmutable();
        $entry = [
            'id'         => bin2hex(random_bytes(8)),
            'timestamp'  => $now->format('Y-m-d H:i:s.u'),
            'event'      => $event,
            'actor'      => $this->actor,
            'ip'         => self::clientIp(),
            'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 250),
            'context'    => $context,
        ];

        $file = $this->logDir . '/' . $now->format('Y-m-d') . '.jsonl';
        $line = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

        $fp = fopen($file, 'a');
        if ($fp === false) {
            // Audit log yazılamıyorsa hata fırlatma — silent fail (kullanıcı işlemini bozmasın)
            return;
        }
        try {
            flock($fp, LOCK_EX);
            fwrite($fp, $line);
            fflush($fp);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /**
     * Belirli sayıda son audit kaydını okur (en yeni en başta).
     *
     * @return array<int, array<string,mixed>>
     */
    public function tail(int $limit = 200): array
    {
        $files = glob($this->logDir . '/*.jsonl') ?: [];
        rsort($files);
        $entries = [];
        foreach ($files as $file) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            $lines = array_reverse($lines);
            foreach ($lines as $line) {
                $decoded = json_decode($line, true);
                if (is_array($decoded)) {
                    $entries[] = $decoded;
                    if (count($entries) >= $limit) return $entries;
                }
            }
        }
        return $entries;
    }

    public function totalCount(): int
    {
        $files = glob($this->logDir . '/*.jsonl') ?: [];
        $sum = 0;
        foreach ($files as $f) {
            $sum += (int) shell_exec('wc -l < ' . escapeshellarg($f)) ?: 0;
        }
        return $sum;
    }

    /** @return array<string,int>  event => count */
    public function countByEvent(): array
    {
        $entries = $this->tail(5000);
        $counts = [];
        foreach ($entries as $e) {
            $key = $e['event'] ?? 'unknown';
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }
        arsort($counts);
        return $counts;
    }

    private static function resolveActor(): string
    {
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['auth']['username'])) {
            return $_SESSION['auth']['username'];
        }
        return 'anonymous';
    }

    private static function clientIp(): string
    {
        $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($forwarded !== '') {
            $first = trim(explode(',', $forwarded)[0]);
            if (filter_var($first, FILTER_VALIDATE_IP)) return $first;
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

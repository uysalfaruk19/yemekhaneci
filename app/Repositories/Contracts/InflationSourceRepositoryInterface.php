<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Enflasyon kaynak repository sözleşmesi (Faz 0.5 → Faz 1.0a geçişi için).
 *
 * Faz 0.5: `App\Repositories\InflationSourceRepository` (JSON-file backed)
 * Faz 1.0a: `App\Repositories\Eloquent\InflationSourceEloquentRepository` (DB)
 *
 * Controller ve servisler bu interface'i type-hint'leyecek; container ya da
 * factory ile concrete impl seçilecek (`config/dependencies.php`'de bind).
 */
interface InflationSourceRepositoryInterface
{
    /** Tüm kaynaklar (resmî 4 + admin'in özel formülleri), display_order'a göre. */
    public function all(): array;

    public function find(string $code): ?array;

    public function findCustom(string $code): ?array;

    /** Yeni özel kaynak oluştur. */
    public function createCustom(array $data, string $createdBy): array;

    /** Var olan özel kaynağı güncelle. */
    public function updateCustom(string $code, array $data, string $updatedBy): array;

    public function deleteCustom(string $code): void;

    /** Aylık endeks değeri ekle/güncelle (UPSERT). */
    public function addMonthlyValue(string $code, int $year, int $month, float $value, ?string $notes, string $enteredBy): void;

    public function deleteMonthlyValue(string $code, string $periodKey): void;

    /** @return array<string, array{value:float, notes:?string, entered_by:string, entered_at:string}> */
    public function monthlyValues(string $code): array;

    /** Calculator için custom kaynakların aylık serisi. */
    public function customMonthlySeries(): array;

    /** Resmî kaynak için aylık değer set (EVDS fetcher). */
    public function setOfficialMonthlyValue(string $code, string $period, float $value, string $enteredBy, ?string $notes = null): void;

    public function officialMonthlyValues(string $code): array;

    public function officialMonthlySeries(): array;

    /** EVDS son çalışma damgası okuma/yazma. */
    public function evdsRunMeta(): array;

    public function recordEvdsRun(string $status, string $message): void;

    /** Custom oluştururken rezerve önek kontrolü. */
    public function isReservedCode(string $code): bool;
}

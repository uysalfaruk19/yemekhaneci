<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Hesaplama kayıtları repo sözleşmesi (anonim + KVKK lead).
 * Faz 1.0a'da Eloquent `InflationCalculation` modeli ile değiştirilir.
 */
interface InflationCalculationRepositoryInterface
{
    public function create(array $data): array;

    /** @return array<int, array<string,mixed>> */
    public function leads(int $limit = 100): array;

    public function totalCount(): int;

    public function leadCount(): int;

    /** @return array<string,int> */
    public function countByPanel(): array;
}

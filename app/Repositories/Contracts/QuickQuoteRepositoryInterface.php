<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * Hızlı teklif repository sözleşmesi.
 * Faz 1.0a'da `requests` + `request_items` tablolarına taşınır.
 */
interface QuickQuoteRepositoryInterface
{
    public function create(array $data): array;

    /** @return array<int, array<string,mixed>> */
    public function recent(int $limit = 100): array;

    public function totalCount(): int;

    public function last7DaysCount(): int;
}

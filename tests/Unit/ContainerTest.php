<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Bootstrap\Container;
use App\Repositories\Contracts\InflationCalculationRepositoryInterface;
use App\Repositories\Contracts\InflationSourceRepositoryInterface;
use App\Repositories\Contracts\QuickQuoteRepositoryInterface;
use App\Repositories\InflationCalculationRepository;
use App\Repositories\InflationSourceRepository;
use App\Repositories\QuickQuoteRepository;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ContainerTest extends TestCase
{
    protected function setUp(): void
    {
        Container::reset();
    }

    public function test_default_bindings(): void
    {
        Container::bootDefaults();

        $this->assertInstanceOf(
            InflationSourceRepository::class,
            Container::get(InflationSourceRepositoryInterface::class)
        );
        $this->assertInstanceOf(
            InflationCalculationRepository::class,
            Container::get(InflationCalculationRepositoryInterface::class)
        );
        $this->assertInstanceOf(
            QuickQuoteRepository::class,
            Container::get(QuickQuoteRepositoryInterface::class)
        );
    }

    public function test_singleton_davranisi(): void
    {
        Container::bootDefaults();
        $a = Container::get(InflationSourceRepositoryInterface::class);
        $b = Container::get(InflationSourceRepositoryInterface::class);
        $this->assertSame($a, $b, 'Aynı instance dönmeli (singleton)');
    }

    public function test_custom_bind_override(): void
    {
        Container::bootDefaults();
        $mock = new class implements InflationSourceRepositoryInterface {
            public function all(): array { return []; }
            public function find(string $code): ?array { return null; }
            public function findCustom(string $code): ?array { return null; }
            public function createCustom(array $data, string $createdBy): array { return []; }
            public function updateCustom(string $code, array $data, string $updatedBy): array { return []; }
            public function deleteCustom(string $code): void {}
            public function addMonthlyValue(string $code, int $year, int $month, float $value, ?string $notes, string $enteredBy): void {}
            public function deleteMonthlyValue(string $code, string $periodKey): void {}
            public function monthlyValues(string $code): array { return []; }
            public function customMonthlySeries(): array { return []; }
            public function setOfficialMonthlyValue(string $code, string $period, float $value, string $enteredBy, ?string $notes = null): void {}
            public function officialMonthlyValues(string $code): array { return []; }
            public function officialMonthlySeries(): array { return []; }
            public function evdsRunMeta(): array { return []; }
            public function recordEvdsRun(string $status, string $message): void {}
            public function isReservedCode(string $code): bool { return false; }
        };
        Container::bind(InflationSourceRepositoryInterface::class, fn() => $mock);

        $resolved = Container::get(InflationSourceRepositoryInterface::class);
        $this->assertSame($mock, $resolved, 'Override edilen mock dönmeli');
    }

    public function test_baglama_yoksa_hata(): void
    {
        $this->expectException(RuntimeException::class);
        Container::get('UnknownInterface');
    }
}

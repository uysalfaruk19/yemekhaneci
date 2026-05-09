<?php

declare(strict_types=1);

namespace App\Bootstrap;

use App\Repositories\Contracts\InflationCalculationRepositoryInterface;
use App\Repositories\Contracts\InflationSourceRepositoryInterface;
use App\Repositories\Contracts\QuickQuoteRepositoryInterface;
use App\Repositories\InflationCalculationRepository;
use App\Repositories\InflationSourceRepository;
use App\Repositories\QuickQuoteRepository;
use Closure;
use RuntimeException;

/**
 * Minimum DI container — interface → factory eşlemesi (singleton).
 *
 * Faz 0.5: file-backed repo'lar
 * Faz 1.0a'da Laravel kuruldukten sonra bu sınıf `app()->bind()` ile değiştirilir.
 *
 * Kullanım:
 *   $repo = Container::get(InflationSourceRepositoryInterface::class);
 */
final class Container
{
    /** @var array<string, Closure> */
    private static array $bindings = [];
    /** @var array<string, object> */
    private static array $instances = [];

    public static function bootDefaults(): void
    {
        if (self::$bindings !== []) return;

        self::bind(InflationSourceRepositoryInterface::class,
            static fn() => new InflationSourceRepository());
        self::bind(InflationCalculationRepositoryInterface::class,
            static fn() => new InflationCalculationRepository());
        self::bind(QuickQuoteRepositoryInterface::class,
            static fn() => new QuickQuoteRepository());
    }

    public static function bind(string $abstract, Closure $factory): void
    {
        self::$bindings[$abstract] = $factory;
        unset(self::$instances[$abstract]);
    }

    /** Singleton resolve. */
    public static function get(string $abstract): object
    {
        if (isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }
        if (!isset(self::$bindings[$abstract])) {
            throw new RuntimeException("Bağlama bulunamadı: {$abstract}");
        }
        $instance = (self::$bindings[$abstract])();
        self::$instances[$abstract] = $instance;
        return $instance;
    }

    /** Test için sıfırlama. */
    public static function reset(): void
    {
        self::$bindings = [];
        self::$instances = [];
    }
}

<?php

declare(strict_types=1);

/**
 * Yemekhaneci.com.tr — front controller (Faz 0.5 demo).
 *
 * Faz 1.0a'da bu dosya Laravel'in `bootstrap/app.php` üzerinden çalışan
 * minik bir başlatıcıya dönüşecek; controller'lar ve servisler korunacak.
 */

use App\Auth\SimpleAuth;
use App\Controllers\Admin\DashboardController as AdminDashboard;
use App\Controllers\Admin\InflationController as AdminInflation;
use App\Controllers\Auth\AuthController;
use App\Controllers\Public\HomeController;
use App\Controllers\Public\InflationCalculatorController;
use App\Controllers\Supplier\DashboardController as SupplierDashboard;
use App\Controllers\Supplier\InflationController as SupplierInflation;
use App\Http\Router;
use App\Middleware\AuthMiddleware;

// ---- Bootstrap ----
$basePath = dirname(__DIR__);
require $basePath . '/app/Helpers/functions.php';

// Basit PSR-4 autoloader (Faz 1.0a'da Composer autoloader devralır).
spl_autoload_register(static function (string $class) use ($basePath): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = str_replace(['App\\', '\\'], ['', '/'], $class);
    $file = $basePath . '/app/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// Hata gösterimi (production'da kapatılır — .env APP_DEBUG'a bağlı olacak).
ini_set('display_errors', '1');
error_reporting(E_ALL);

SimpleAuth::startSession();

// ---- Router ----
$router = new Router();

// --- Public ---
$router->get('/', static fn() => (new HomeController())->index());

$router->get(
    '/araclar/enflasyon-hesaplayici',
    static fn() => (new InflationCalculatorController())->show()
);
$router->post(
    '/api/v1/enflasyon/hesapla',
    static fn() => (new InflationCalculatorController())->calculate()
);

// --- Auth ---
$router->get('/giris-yap', static fn() => (new AuthController())->showLogin(), [AuthMiddleware::guestOnly()]);
$router->post('/giris-yap', static fn() => (new AuthController())->login(),    [AuthMiddleware::guestOnly()]);
$router->get('/cikis-yap', static fn() => (new AuthController())->logout());
$router->post('/cikis-yap', static fn() => (new AuthController())->logout());

// --- Yemekçi paneli (auth + role=supplier) ---
$router->get(
    '/yemekci',
    static fn() => (new SupplierDashboard())->index(),
    [AuthMiddleware::requireRole('supplier')]
);
$router->get(
    '/yemekci/araclar/enflasyon',
    static fn() => (new SupplierInflation())->show(),
    [AuthMiddleware::requireRole('supplier')]
);

// --- Admin paneli (auth + role=admin) ---
$router->get(
    '/yonetim',
    static fn() => (new AdminDashboard())->index(),
    [AuthMiddleware::requireRole('admin')]
);
$router->get(
    '/yonetim/araclar/enflasyon',
    static fn() => (new AdminInflation())->show(),
    [AuthMiddleware::requireRole('admin')]
);
$router->get(
    '/yonetim/sistem/enflasyon-kaynaklari',
    static fn() => (new AdminInflation())->sources(),
    [AuthMiddleware::requireRole('admin')]
);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);

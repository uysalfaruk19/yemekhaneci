<?php

declare(strict_types=1);

/**
 * Yemekhaneci.com.tr — front controller (Faz 0.5 demo).
 *
 * Faz 1.0a'da bu dosya Laravel'in `bootstrap/app.php` üzerinden çalışan
 * minik bir başlatıcıya dönüşecek; controller'lar ve servisler korunacak.
 */

use App\Auth\SimpleAuth;
use App\Controllers\Auth\TwoFactorController;
use App\Controllers\Admin\AuditLogController as AdminAuditLog;
use App\Controllers\Admin\DashboardController as AdminDashboard;
use App\Controllers\Admin\InflationController as AdminInflation;
use App\Controllers\Admin\InflationLeadsController as AdminInflationLeads;
use App\Controllers\Admin\InflationSourcesController as AdminInflationSources;
use App\Controllers\Auth\AuthController;
use App\Controllers\Public\HealthController;
use App\Controllers\Public\HomeController;
use App\Controllers\Public\InflationCalculatorController;
use App\Controllers\Public\LegalController;
use App\Controllers\Public\MarketingController;
use App\Controllers\Public\QuickQuoteController;
use App\Controllers\Public\SupplierApplicationController;
use App\Controllers\Admin\QuickQuotesController as AdminQuickQuotes;
use App\Controllers\Supplier\DashboardController as SupplierDashboard;
use App\Controllers\Supplier\InflationController as SupplierInflation;
use App\Http\Router;
use App\Middleware\AuthMiddleware;

// ---- Bootstrap ----
$basePath = dirname(__DIR__);

// Composer autoloader varsa onu kullan (PSR-4 + helpers files autoload).
$composerAutoload = $basePath . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
} else {
    // Fallback: manuel autoloader (composer install yapılmamışsa)
    require $basePath . '/app/Helpers/functions.php';
    spl_autoload_register(static function (string $class) use ($basePath): void {
        if (!str_starts_with($class, 'App\\')) return;
        $relative = str_replace(['App\\', '\\'], ['', '/'], $class);
        $file = $basePath . '/app/' . $relative . '.php';
        if (is_file($file)) require $file;
    });
}

// Debug modu .env'den (vlucas/phpdotenv Faz 1.0a'da bind edilecek)
$appDebug = (getenv('APP_DEBUG') ?: 'false') === 'true';
ini_set('display_errors', $appDebug ? '1' : '0');
ini_set('display_startup_errors', $appDebug ? '1' : '0');
error_reporting(E_ALL);

// Logger + uncaught exception/error handler (Monolog rotating)
if (class_exists(\App\Bootstrap\Logger::class)) {
    \App\Bootstrap\Logger::registerHandlers(showDebug: $appDebug);
}

// Security headers (HSTS, CSP, X-Frame-Options, vb.) + HTTPS zorunluluğu
if (class_exists(\App\Bootstrap\SecurityHeaders::class)) {
    \App\Bootstrap\SecurityHeaders::send();
}

// DI container (Faz 1.0a'da Laravel container'a taşınır)
if (class_exists(\App\Bootstrap\Container::class)) {
    \App\Bootstrap\Container::bootDefaults();
}

SimpleAuth::startSession();

// ---- Router ----
$router = new Router();

// --- Health checks (Hostinger/Traefik için) ---
$router->get('/saglik', static fn() => (new HealthController())->liveness());
$router->get('/hazir',  static fn() => (new HealthController())->readiness());

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
$router->post(
    '/api/v1/enflasyon/mail-gonder',
    static fn() => (new InflationCalculatorController())->submitLead()
);
$router->post(
    '/api/v1/hizli-teklif',
    static fn() => (new QuickQuoteController())->submit()
);

// --- Marketing & başvuru sayfaları ---
$router->get('/toplu-yemek',    static fn() => (new MarketingController())->topluYemek());
$router->get('/yemekciler',     static fn() => (new MarketingController())->yemekciler());
$router->get('/nasil-calisir',  static fn() => (new MarketingController())->nasilCalisir());
$router->get('/yemekci-ol',     static fn() => (new SupplierApplicationController())->showForm());
$router->post('/yemekci-ol',    static fn() => (new SupplierApplicationController())->submit());

// --- Legal (KVKK + Çerez) ---
$router->get('/yasal/aydinlatma-metni',  static fn() => (new LegalController())->privacyNotice());
$router->get('/yasal/cerez-politikasi',  static fn() => (new LegalController())->cookiePolicy());
$router->get('/yasal/kullanim-kosullari',static fn() => (new LegalController())->termsOfService());
$router->get('/yasal/veri-silme',        static fn() => (new LegalController())->dataDeletion());

// --- Auth ---
$router->get('/giris-yap', static fn() => (new AuthController())->showLogin(), [AuthMiddleware::guestOnly()]);
$router->post('/giris-yap', static fn() => (new AuthController())->login(),    [AuthMiddleware::guestOnly()]);
$router->get('/cikis-yap', static fn() => (new AuthController())->logout());
$router->post('/cikis-yap', static fn() => (new AuthController())->logout());

// --- 2FA (TOTP) ---
$router->get('/giris-yap/2fa',  static fn() => (new TwoFactorController())->showChallenge());
$router->post('/giris-yap/2fa', static fn() => (new TwoFactorController())->verifyChallenge());
// Auth gereken self-servis 2FA ayarları (admin + supplier)
$router->get('/hesap/iki-adimli-dogrulama',
    static fn() => (new TwoFactorController())->showSetup(),
    [AuthMiddleware::requireAuth()]);
$router->post('/hesap/iki-adimli-dogrulama/onayla',
    static fn() => (new TwoFactorController())->confirmSetupAndRender(),
    [AuthMiddleware::requireAuth()]);
$router->post('/hesap/iki-adimli-dogrulama/kapat',
    static fn() => (new TwoFactorController())->disable(),
    [AuthMiddleware::requireAuth()]);

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
$router->get('/yonetim/enflasyon/lead-ler',
    static fn() => (new AdminInflationLeads())->index(),
    [AuthMiddleware::requireRole('admin')]
);
$router->get('/yonetim/hizli-teklifler',
    static fn() => (new AdminQuickQuotes())->index(),
    [AuthMiddleware::requireRole('admin')]
);
$router->get('/yonetim/sistem/audit-log',
    static fn() => (new AdminAuditLog())->index(),
    [AuthMiddleware::requireRole('admin')]
);

// --- Admin: Enflasyon kaynak yönetimi (Faz 0.5.12) ---
$adminMw = [AuthMiddleware::requireRole('admin')];
$router->get('/yonetim/sistem/enflasyon-kaynaklari',
    static fn() => (new AdminInflationSources())->index(), $adminMw);
$router->post('/yonetim/sistem/enflasyon-kaynaklari/evds-tetikle',
    static fn() => (new AdminInflationSources())->triggerEvds(), $adminMw);
$router->get('/yonetim/sistem/enflasyon-kaynaklari/yeni',
    static fn() => (new AdminInflationSources())->createForm(), $adminMw);
$router->post('/yonetim/sistem/enflasyon-kaynaklari',
    static fn() => (new AdminInflationSources())->store(), $adminMw);
$router->get('/yonetim/sistem/enflasyon-kaynaklari/{code}/duzenle',
    static fn(array $p) => (new AdminInflationSources())->editForm($p), $adminMw);
$router->post('/yonetim/sistem/enflasyon-kaynaklari/{code}/duzenle',
    static fn(array $p) => (new AdminInflationSources())->update($p), $adminMw);
$router->post('/yonetim/sistem/enflasyon-kaynaklari/{code}/sil',
    static fn(array $p) => (new AdminInflationSources())->delete($p), $adminMw);
$router->get('/yonetim/sistem/enflasyon-kaynaklari/{code}/aylik-veri',
    static fn(array $p) => (new AdminInflationSources())->dataView($p), $adminMw);
$router->post('/yonetim/sistem/enflasyon-kaynaklari/{code}/aylik-veri',
    static fn(array $p) => (new AdminInflationSources())->dataAdd($p), $adminMw);
$router->post('/yonetim/sistem/enflasyon-kaynaklari/{code}/aylik-veri/{period}/sil',
    static fn(array $p) => (new AdminInflationSources())->dataDelete($p), $adminMw);

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);

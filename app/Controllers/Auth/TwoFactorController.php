<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Auth\SimpleAuth;
use App\Repositories\TotpSecretRepository;
use App\Services\AuditLogger;
use App\Services\TotpService;

/**
 * 2FA (TOTP) akışları:
 * - challenge: login sonrası 6 haneli kod iste
 * - setup: secret + QR + ilk kod doğrulama → enable + recovery codes göster
 * - disable: mevcut TOTP veya recovery code ile devre dışı bırak
 */
final class TwoFactorController
{
    public function showChallenge(): string
    {
        if (!SimpleAuth::isPending2fa()) {
            // Normal yetkili veya hiç login yok — direkt yönlendir
            \redirect(SimpleAuth::user()['panel_route'] ?? '/');
        }
        $content = \view('auth.two-factor-challenge', [
            'username'           => SimpleAuth::pendingUsername(),
            'recovery_remaining' => (new TotpSecretRepository())->recoveryCodesRemaining(SimpleAuth::pendingUsername() ?? ''),
            'error'              => \flash('2fa_error'),
        ]);
        return \layout('auth', $content, ['title' => 'İki adımlı doğrulama']);
    }

    public function verifyChallenge(): void
    {
        if (!SimpleAuth::isPending2fa()) \redirect('/giris-yap');
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('2fa_error', 'Oturum doğrulaması başarısız.');
            \redirect('/giris-yap/2fa');
        }

        $username = SimpleAuth::pendingUsername();
        $token = preg_replace('/\s+/', '', (string) ($_POST['token'] ?? '')) ?? '';
        $useRecovery = !empty($_POST['use_recovery']);

        $repo = new TotpSecretRepository();

        $verified = false;
        if ($useRecovery) {
            $verified = $repo->consumeRecoveryCode($username ?? '', $token);
        } else {
            $secret = $repo->getSecret($username ?? '');
            if ($secret) {
                $verified = TotpService::verify($secret, $token, discrepancy: 1);
            }
        }

        if (!$verified) {
            AuditLogger::log('auth.2fa_failed', ['username' => $username, 'used_recovery' => $useRecovery]);
            \flash('2fa_error', $useRecovery ? 'Geçersiz kurtarma kodu.' : 'Geçersiz veya süresi dolmuş kod.');
            \redirect('/giris-yap/2fa');
        }

        SimpleAuth::complete2fa();
        AuditLogger::log('auth.2fa_success', ['username' => $username, 'used_recovery' => $useRecovery]);
        $user = SimpleAuth::user();
        \flash('success', 'Hoş geldin, ' . $user['display_name'] . '.');
        \redirect($user['panel_route']);
    }

    public function showSetup(): string
    {
        $user = SimpleAuth::user();
        if (!$user) \redirect('/giris-yap');

        $username = $user['username'];
        $repo = new TotpSecretRepository();

        if ($repo->isEnabled($username)) {
            $content = \view('auth.two-factor-status', [
                'enabled' => true,
                'enabled_at' => $repo->find($username)['enabled_at'] ?? null,
                'recovery_remaining' => $repo->recoveryCodesRemaining($username),
            ]);
            return \layout('app', $content, ['title' => 'İki adımlı doğrulama', 'authed' => true, 'user' => $user]);
        }

        // Yeni veya tamamlanmamış setup — secret üret/yükle
        $existing = $repo->find($username);
        $secret = $existing['secret'] ?? null;
        if (!$secret) {
            $secret = TotpService::generateSecret();
            $repo->startSetup($username, $secret);
        }

        $uri = TotpService::provisioningUri($secret, $user['email'] ?? $username);
        $qrUrl = TotpService::qrCodeImageUrl($uri);

        $content = \view('auth.two-factor-setup', [
            'secret'     => $secret,
            'uri'        => $uri,
            'qr_url'     => $qrUrl,
            'error'      => \flash('2fa_error'),
            'user'       => $user,
        ]);
        return \layout('app', $content, ['title' => '2FA Kurulumu', 'authed' => true, 'user' => $user]);
    }

    public function confirmSetup(): void
    {
        $user = SimpleAuth::user();
        if (!$user) \redirect('/giris-yap');
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('2fa_error', 'Oturum doğrulaması başarısız.');
            \redirect('/hesap/iki-adimli-dogrulama');
        }

        $repo = new TotpSecretRepository();
        $username = $user['username'];
        $secret = $repo->getSecret($username);
        $token = preg_replace('/\s+/', '', (string) ($_POST['token'] ?? '')) ?? '';

        if (!$secret || !TotpService::verify($secret, $token)) {
            \flash('2fa_error', 'Doğrulama başarısız. Authenticator uygulamanızdaki güncel kodu girin.');
            \redirect('/hesap/iki-adimli-dogrulama');
        }

        $codes = $repo->enable($username);
        AuditLogger::log('auth.2fa_enabled', ['username' => $username]);

        $content = \view('auth.two-factor-recovery-codes', [
            'codes'    => $codes,
            'username' => $username,
        ]);
        return; // Render edilemiyor — view'i echo ile basıyoruz
    }

    /**
     * Setup confirm sonrası recovery code'ları göstermek için ayrı action.
     * confirmSetup view echo edemediği için redirect yerine render dönmeli.
     */
    public function confirmSetupAndRender(): string
    {
        $user = SimpleAuth::user();
        if (!$user) \redirect('/giris-yap');
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('2fa_error', 'Oturum doğrulaması başarısız.');
            \redirect('/hesap/iki-adimli-dogrulama');
        }

        $repo = new TotpSecretRepository();
        $username = $user['username'];
        $secret = $repo->getSecret($username);
        $token = preg_replace('/\s+/', '', (string) ($_POST['token'] ?? '')) ?? '';

        if (!$secret || !TotpService::verify($secret, $token)) {
            \flash('2fa_error', 'Doğrulama başarısız. Authenticator uygulamanızdaki güncel kodu girin.');
            \redirect('/hesap/iki-adimli-dogrulama');
        }

        $codes = $repo->enable($username);
        AuditLogger::log('auth.2fa_enabled', ['username' => $username]);

        $content = \view('auth.two-factor-recovery-codes', [
            'codes'    => $codes,
            'username' => $username,
        ]);
        return \layout('app', $content, ['title' => '2FA Kurtarma Kodları', 'authed' => true, 'user' => $user]);
    }

    public function disable(): void
    {
        $user = SimpleAuth::user();
        if (!$user) \redirect('/giris-yap');
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('2fa_error', 'Oturum doğrulaması başarısız.');
            \redirect('/hesap/iki-adimli-dogrulama');
        }

        $repo = new TotpSecretRepository();
        $username = $user['username'];
        $token = preg_replace('/\s+/', '', (string) ($_POST['token'] ?? '')) ?? '';

        $secret = $repo->getSecret($username);
        if (!$secret || !TotpService::verify($secret, $token)) {
            \flash('2fa_error', '2FA kapatma için güncel kod gerekir.');
            \redirect('/hesap/iki-adimli-dogrulama');
        }

        $repo->disable($username);
        AuditLogger::log('auth.2fa_disabled', ['username' => $username]);
        \flash('success', 'İki adımlı doğrulama devre dışı bırakıldı.');
        \redirect('/hesap/iki-adimli-dogrulama');
    }
}

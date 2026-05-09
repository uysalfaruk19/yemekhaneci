<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Auth\SimpleAuth;
use App\Repositories\SupplierApplicationRepository;
use App\Services\AuditLogger;
use App\Services\RateLimiter;

/**
 * Yemekçi başvuru akışı (`/yemekci-ol`).
 * GET → form, POST → kayıt + referans no.
 * Admin /yonetim/yemekci-basvurulari altında onay bekleyenler listesini görür.
 */
final class SupplierApplicationController
{
    public function showForm(): string
    {
        $content = \view('marketing.yemekci-ol', [
            'success'   => \flash('supplier_app_success'),
            'reference' => \flash('supplier_app_reference'),
            'error'     => \flash('supplier_app_error'),
        ]);
        return \layout('app', $content, [
            'title' => 'Yemekçi Olarak Başvur — Yemekhaneci',
            'description' => 'Catering / yemek firmanızı Yemekhaneci.com.tr platformuna kaydedin. KYC + sertifika doğrulama sonrası 7-14 günde aktif.',
            'authed' => SimpleAuth::check(),
            'user'   => SimpleAuth::user(),
        ]);
    }

    public function submit(): void
    {
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('supplier_app_error', 'Oturum doğrulaması başarısız.');
            \redirect('/yemekci-ol');
        }

        $rl = new RateLimiter('supplier_application', limit: 3, windowSeconds: 3600);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!$rl->allow($ip)) {
            \flash('supplier_app_error', 'Saatlik başvuru limitiniz doldu. 1 saat sonra tekrar deneyin.');
            \redirect('/yemekci-ol');
        }

        $errors = [];
        $companyName = trim((string) ($_POST['company_name'] ?? ''));
        if (mb_strlen($companyName) < 3 || mb_strlen($companyName) > 200) {
            $errors[] = 'Firma unvanı 3-200 karakter olmalı.';
        }

        $taxNumber = trim((string) ($_POST['tax_number'] ?? ''));
        if (!preg_match('/^\d{10,11}$/', $taxNumber)) {
            $errors[] = 'Vergi numarası 10 (kurumsal) veya 11 (TC) hane olmalı.';
        }

        $email = trim((string) ($_POST['contact_email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Geçerli bir e-posta giriniz.';
        }

        $phone = trim((string) ($_POST['contact_phone'] ?? ''));
        if (!preg_match('/^[\d\s+()-]{7,25}$/', $phone)) {
            $errors[] = 'Geçerli bir telefon giriniz.';
        }

        $contactName = trim((string) ($_POST['contact_name'] ?? ''));
        if (mb_strlen($contactName) < 3) $errors[] = 'Yetkili adı 3+ karakter olmalı.';

        $city = trim((string) ($_POST['city'] ?? ''));
        if (mb_strlen($city) < 2) $errors[] = 'Şehir gerekli.';

        $capacity = (int) ($_POST['daily_capacity'] ?? 0);
        if ($capacity < 50 || $capacity > 100000) {
            $errors[] = 'Günlük kapasite 50-100.000 öğün arasında olmalı.';
        }

        if (empty($_POST['kvkk'])) $errors[] = 'KVKK onayı zorunlu.';

        if ($errors) {
            \flash('supplier_app_error', implode(' · ', $errors));
            \flash_old($_POST);
            \redirect('/yemekci-ol');
        }

        $record = (new SupplierApplicationRepository())->create([
            'company_name'       => $companyName,
            'tax_number'         => $taxNumber,
            'contact_name'       => $contactName,
            'contact_email'      => $email,
            'contact_phone'      => $phone,
            'city'               => $city,
            'district'           => trim((string) ($_POST['district'] ?? '')),
            'years_in_business'  => (int) ($_POST['years_in_business'] ?? 0),
            'daily_capacity'     => $capacity,
            'service_areas'      => array_map('trim', explode(',', (string) ($_POST['service_areas'] ?? ''))),
            'certifications'     => array_values($_POST['certifications'] ?? []),
            'website'            => trim((string) ($_POST['website'] ?? '')),
            'notes'              => trim((string) ($_POST['notes'] ?? '')),
            'ip_address'         => $ip,
            'user_agent'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);

        AuditLogger::log('supplier_application.submitted', [
            'reference'   => $record['reference'],
            'company'     => $companyName,
            'city'        => $city,
            'capacity'    => $capacity,
        ]);

        \flash('supplier_app_success', 'Başvurunuz alındı. KYC ekibimiz 7-14 iş günü içinde sizinle iletişime geçecek.');
        \flash('supplier_app_reference', $record['reference']);
        \redirect('/yemekci-ol');
    }
}

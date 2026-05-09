<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Auth\SimpleAuth;
use App\Repositories\SupplierApplicationRepository;
use App\Services\AuditLogger;

/**
 * Admin yemekçi yönetimi.
 *  - /yonetim/yemekciler                 (liste + filtre + KPI)
 *  - /yonetim/yemekciler/yeni            (manuel ekleme formu)
 *  - /yonetim/yemekciler/{id}/duzenle    (düzenleme + fiyat ayarları)
 *  - POST .../onayla, .../askiya-al, .../reddet, .../sil   (hızlı aksiyonlar)
 */
final class SuppliersController
{
    private SupplierApplicationRepository $repo;

    public function __construct()
    {
        $this->repo = new SupplierApplicationRepository();
    }

    public function index(): string
    {
        $filter = (string) ($_GET['durum'] ?? 'all');
        $all = $this->repo->recent(500);

        $filtered = $filter === 'all'
            ? $all
            : array_values(array_filter($all, static fn(array $r) => ($r['status'] ?? '') === $filter));

        $content = \view('admin.suppliers.index', [
            'suppliers'      => $filtered,
            'filter'         => $filter,
            'total_count'    => $this->repo->totalCount(),
            'pending_count'  => $this->repo->pendingCount(),
            'active_count'   => $this->repo->activeCount(),
            'flash_success'  => \flash('supplier_success'),
            'flash_error'    => \flash('supplier_error'),
        ]);

        return \layout('app', $content, [
            'title'  => 'Yemekçi Yönetimi — Yönetim',
            'authed' => true,
            'user'   => SimpleAuth::user(),
        ]);
    }

    public function createForm(): string
    {
        $content = \view('admin.suppliers.form', [
            'mode'       => 'create',
            'record'     => $this->emptyRecord(),
            'errors'     => $this->popErrors(),
            'pricing'    => SupplierApplicationRepository::defaultPricing(),
        ]);
        return \layout('app', $content, [
            'title'  => 'Yeni Yemekçi Ekle — Yönetim',
            'authed' => true,
            'user'   => SimpleAuth::user(),
        ]);
    }

    public function store(): void
    {
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('supplier_error', 'Oturum doğrulaması başarısız.');
            \redirect('/yonetim/yemekciler/yeni');
        }

        $errors = $this->validate($_POST);
        if ($errors) {
            $this->bagErrors($errors, $_POST);
            \redirect('/yonetim/yemekciler/yeni');
        }

        $payload = $this->collectFields($_POST);
        $payload['status'] = 'manual';
        $payload['anonymous_code'] = $this->repo->nextAnonymousCode();

        $record = $this->repo->create($payload);
        $this->repo->setStatus((int) $record['id'], 'manual', SimpleAuth::user()['username'] ?? 'admin');

        AuditLogger::log('supplier.manual_created', ['id' => $record['id'], 'company' => $record['company_name'], 'code' => $record['anonymous_code']]);

        \flash('supplier_success', "Yemekçi eklendi: {$record['company_name']} (kod: {$record['anonymous_code']}).");
        \redirect('/yonetim/yemekciler/' . $record['id'] . '/duzenle');
    }

    public function editForm(array $params): string
    {
        $id = (int) ($params['id'] ?? 0);
        $record = $this->repo->find($id);
        if (!$record) {
            http_response_code(404);
            return \view('errors.404');
        }

        $content = \view('admin.suppliers.form', [
            'mode'    => 'edit',
            'record'  => $record,
            'errors'  => $this->popErrors(),
            'pricing' => $record['pricing'] ?? SupplierApplicationRepository::defaultPricing(),
        ]);
        return \layout('app', $content, [
            'title'  => 'Yemekçi Düzenle — Yönetim',
            'authed' => true,
            'user'   => SimpleAuth::user(),
        ]);
    }

    public function update(array $params): void
    {
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('supplier_error', 'Oturum doğrulaması başarısız.');
            \redirect('/yonetim/yemekciler');
        }
        $id = (int) ($params['id'] ?? 0);
        if (!$this->repo->find($id)) {
            \flash('supplier_error', 'Yemekçi bulunamadı.');
            \redirect('/yonetim/yemekciler');
        }

        $errors = $this->validate($_POST);
        if ($errors) {
            $this->bagErrors($errors, $_POST);
            \redirect('/yonetim/yemekciler/' . $id . '/duzenle');
        }

        $payload = $this->collectFields($_POST);
        $this->repo->update($id, $payload, SimpleAuth::user()['username'] ?? 'admin');

        AuditLogger::log('supplier.updated', ['id' => $id, 'company' => $payload['company_name']]);

        \flash('supplier_success', 'Yemekçi güncellendi: ' . $payload['company_name']);
        \redirect('/yonetim/yemekciler/' . $id . '/duzenle');
    }

    public function approve(array $params): void
    {
        $this->statusAction($params, 'active', 'Yemekçi yayına alındı.');
    }

    public function suspend(array $params): void
    {
        $this->statusAction($params, 'suspended', 'Yemekçi askıya alındı.');
    }

    public function reject(array $params): void
    {
        $this->statusAction($params, 'rejected', 'Başvuru reddedildi.');
    }

    public function delete(array $params): void
    {
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('supplier_error', 'Oturum doğrulaması başarısız.');
            \redirect('/yonetim/yemekciler');
        }
        $id = (int) ($params['id'] ?? 0);
        $record = $this->repo->find($id);
        if (!$record) {
            \flash('supplier_error', 'Yemekçi bulunamadı.');
            \redirect('/yonetim/yemekciler');
        }
        $this->repo->delete($id);
        AuditLogger::log('supplier.deleted', ['id' => $id, 'company' => $record['company_name'] ?? '']);
        \flash('supplier_success', 'Yemekçi silindi.');
        \redirect('/yonetim/yemekciler');
    }

    private function statusAction(array $params, string $status, string $successMsg): void
    {
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('supplier_error', 'Oturum doğrulaması başarısız.');
            \redirect('/yonetim/yemekciler');
        }
        $id = (int) ($params['id'] ?? 0);
        $record = $this->repo->find($id);
        if (!$record) {
            \flash('supplier_error', 'Yemekçi bulunamadı.');
            \redirect('/yonetim/yemekciler');
        }

        // Onay (active) verirken anonim kod yoksa ata
        if ($status === 'active' && empty($record['anonymous_code'])) {
            $this->repo->update($id, ['anonymous_code' => $this->repo->nextAnonymousCode()]);
        }

        $this->repo->setStatus($id, $status, SimpleAuth::user()['username'] ?? 'admin');
        AuditLogger::log("supplier.status_{$status}", ['id' => $id, 'company' => $record['company_name'] ?? '']);

        \flash('supplier_success', $successMsg);
        \redirect('/yonetim/yemekciler');
    }

    // ────────────────────────────────────────────────────────

    private function emptyRecord(): array
    {
        return [
            'id' => null,
            'company_name' => '', 'tax_number' => '',
            'contact_name' => '', 'contact_email' => '', 'contact_phone' => '',
            'city' => '', 'district' => '',
            'years_in_business' => 0, 'daily_capacity' => 1000,
            'service_areas' => [], 'certifications' => [],
            'website' => '', 'notes' => '',
            'rating' => 4.5, 'anonymous_code' => null,
            'pricing' => SupplierApplicationRepository::defaultPricing(),
            'status' => 'manual',
        ];
    }

    private function collectFields(array $post): array
    {
        $serviceAreas = array_filter(array_map('trim', explode(',', (string) ($post['service_areas'] ?? ''))));

        return [
            'company_name'      => trim((string) ($post['company_name'] ?? '')),
            'tax_number'        => trim((string) ($post['tax_number'] ?? '')),
            'contact_name'      => trim((string) ($post['contact_name'] ?? '')),
            'contact_email'     => trim((string) ($post['contact_email'] ?? '')),
            'contact_phone'     => trim((string) ($post['contact_phone'] ?? '')),
            'city'              => trim((string) ($post['city'] ?? '')),
            'district'          => trim((string) ($post['district'] ?? '')),
            'years_in_business' => (int) ($post['years_in_business'] ?? 0),
            'daily_capacity'    => (int) ($post['daily_capacity'] ?? 0),
            'service_areas'     => array_values($serviceAreas),
            'certifications'    => array_values($post['certifications'] ?? []),
            'website'           => trim((string) ($post['website'] ?? '')),
            'notes'              => trim((string) ($post['notes'] ?? '')),
            'rating'            => max(0, min(5, (float) ($post['rating'] ?? 4.5))),
            'pricing'           => [
                'ekonomik_per_meal' => max(0, (int) ($post['pricing']['ekonomik_per_meal'] ?? 0)),
                'genel_per_meal'    => max(0, (int) ($post['pricing']['genel_per_meal']    ?? 0)),
                'premium_per_meal'  => max(0, (int) ($post['pricing']['premium_per_meal']  ?? 0)),
                'salad_per_extra'   => max(0, (int) ($post['pricing']['salad_per_extra']   ?? 0)),
                'dessert_addon'     => max(0, (int) ($post['pricing']['dessert_addon']     ?? 0)),
                'drinks_both_addon' => max(0, (int) ($post['pricing']['drinks_both_addon'] ?? 0)),
                'personnel_addon'   => max(0, (int) ($post['pricing']['personnel_addon']   ?? 0)),
                'equipment_addon'   => max(0, (int) ($post['pricing']['equipment_addon']   ?? 0)),
                'saturday_partial'  => max(0, (int) ($post['pricing']['saturday_partial']  ?? 0)),
                'saturday_yes'      => max(0, (int) ($post['pricing']['saturday_yes']      ?? 0)),
                'notes'             => trim((string) ($post['pricing']['notes'] ?? '')),
            ],
        ];
    }

    /** @return array<string, array<int,string>> */
    private function validate(array $post): array
    {
        $errors = [];
        $name = trim((string) ($post['company_name'] ?? ''));
        if (mb_strlen($name) < 3 || mb_strlen($name) > 200) {
            $errors['company_name'][] = 'Firma unvanı 3-200 karakter olmalı.';
        }
        $tax = trim((string) ($post['tax_number'] ?? ''));
        if ($tax !== '' && !preg_match('/^\d{10,11}$/', $tax)) {
            $errors['tax_number'][] = 'Vergi no 10 (kurumsal) veya 11 (TC) hane olmalı.';
        }
        $email = trim((string) ($post['contact_email'] ?? ''));
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['contact_email'][] = 'Geçerli e-posta giriniz.';
        }
        $city = trim((string) ($post['city'] ?? ''));
        if (mb_strlen($city) < 2) {
            $errors['city'][] = 'Şehir gerekli.';
        }
        $cap = (int) ($post['daily_capacity'] ?? 0);
        if ($cap < 0 || $cap > 1000000) {
            $errors['daily_capacity'][] = 'Günlük kapasite makul aralıkta olmalı.';
        }
        $rating = (float) ($post['rating'] ?? 4.5);
        if ($rating < 0 || $rating > 5) {
            $errors['rating'][] = 'Puan 0-5 arasında olmalı.';
        }
        return $errors;
    }

    private function bagErrors(array $errors, array $oldInput): void
    {
        $_SESSION['_form_errors'] = $errors;
        \flash_old($oldInput);
    }

    private function popErrors(): array
    {
        $errors = $_SESSION['_form_errors'] ?? [];
        unset($_SESSION['_form_errors']);
        return $errors;
    }
}

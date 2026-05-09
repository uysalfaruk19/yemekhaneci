<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Auth\SimpleAuth;
use App\Repositories\InflationSourceRepository;

/**
 * Admin enflasyon kaynak yönetimi (Faz 0.5.12 — özel formül CRUD).
 * PRD Bölüm 25.6.3 + 25.7.
 */
final class InflationSourcesController
{
    private InflationSourceRepository $repo;

    public function __construct()
    {
        $this->repo = new InflationSourceRepository();
    }

    public function index(): string
    {
        return $this->render('admin.inflation-sources', [
            'sources' => $this->repo->all(),
            'flash_success' => \flash('source_success'),
            'flash_error'   => \flash('source_error'),
        ]);
    }

    public function createForm(): string
    {
        return $this->render('admin.inflation-source-form', [
            'mode'   => 'create',
            'record' => $this->emptyRecord(),
            'errors' => $this->popErrors(),
            'units'  => InflationSourceRepository::UNITS,
        ]);
    }

    public function store(): void
    {
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('source_error', 'Oturum doğrulaması başarısız.');
            \redirect('/yonetim/sistem/enflasyon-kaynaklari');
        }

        $payload = $this->collectFormFields();
        $errors  = $this->validateSource($payload, isCreate: true);
        if ($errors) {
            $this->bagErrors($errors, $payload);
            \redirect('/yonetim/sistem/enflasyon-kaynaklari/yeni');
        }

        try {
            $record = $this->repo->createCustom($payload, SimpleAuth::user()['username'] ?? 'unknown');
        } catch (\Throwable $e) {
            $this->bagErrors(['code' => [$e->getMessage()]], $payload);
            \redirect('/yonetim/sistem/enflasyon-kaynaklari/yeni');
        }

        \flash('source_success', "Yeni kaynak oluşturuldu: {$record['name']} (kod: {$record['code']}).");
        \redirect('/yonetim/sistem/enflasyon-kaynaklari/' . $record['code'] . '/aylik-veri');
    }

    public function editForm(array $params): string
    {
        $code = $params['code'] ?? '';
        $record = $this->repo->findCustom($code);
        if (!$record) {
            return $this->notFound("Özel kaynak bulunamadı: {$code}");
        }

        return $this->render('admin.inflation-source-form', [
            'mode'   => 'edit',
            'record' => $record,
            'errors' => $this->popErrors(),
            'units'  => InflationSourceRepository::UNITS,
        ]);
    }

    public function update(array $params): void
    {
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('source_error', 'Oturum doğrulaması başarısız.');
            \redirect('/yonetim/sistem/enflasyon-kaynaklari');
        }

        $code = $params['code'] ?? '';
        if (!$this->repo->findCustom($code)) {
            \flash('source_error', 'Düzenlenecek kaynak bulunamadı.');
            \redirect('/yonetim/sistem/enflasyon-kaynaklari');
        }

        $payload = $this->collectFormFields();
        $payload['code'] = $code;  // kodu sabitle
        $errors = $this->validateSource($payload, isCreate: false);
        if ($errors) {
            $this->bagErrors($errors, $payload);
            \redirect('/yonetim/sistem/enflasyon-kaynaklari/' . $code . '/duzenle');
        }

        try {
            $this->repo->updateCustom($code, $payload, SimpleAuth::user()['username'] ?? 'unknown');
        } catch (\Throwable $e) {
            \flash('source_error', $e->getMessage());
            \redirect('/yonetim/sistem/enflasyon-kaynaklari/' . $code . '/duzenle');
        }

        \flash('source_success', 'Kaynak güncellendi: ' . $payload['name']);
        \redirect('/yonetim/sistem/enflasyon-kaynaklari');
    }

    public function delete(array $params): void
    {
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('source_error', 'Oturum doğrulaması başarısız.');
            \redirect('/yonetim/sistem/enflasyon-kaynaklari');
        }

        $code = $params['code'] ?? '';
        try {
            $this->repo->deleteCustom($code);
            \flash('source_success', "Kaynak silindi: {$code}.");
        } catch (\Throwable $e) {
            \flash('source_error', $e->getMessage());
        }
        \redirect('/yonetim/sistem/enflasyon-kaynaklari');
    }

    public function dataView(array $params): string
    {
        $code = $params['code'] ?? '';
        $record = $this->repo->findCustom($code);
        if (!$record) {
            return $this->notFound("Özel kaynak bulunamadı: {$code}");
        }

        return $this->render('admin.inflation-source-data', [
            'record'         => $record,
            'monthly_values' => $this->repo->monthlyValues($code),
            'flash_success'  => \flash('source_success'),
            'flash_error'    => \flash('source_error'),
            'errors'         => $this->popErrors(),
        ]);
    }

    public function dataAdd(array $params): void
    {
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('source_error', 'Oturum doğrulaması başarısız.');
            \redirect('/yonetim/sistem/enflasyon-kaynaklari');
        }

        $code = $params['code'] ?? '';
        if (!$this->repo->findCustom($code)) {
            \flash('source_error', 'Kaynak bulunamadı.');
            \redirect('/yonetim/sistem/enflasyon-kaynaklari');
        }

        $period = trim((string) ($_POST['period'] ?? ''));
        $valueRaw = trim((string) ($_POST['value'] ?? ''));
        $notes = trim((string) ($_POST['notes'] ?? ''));

        $errors = [];
        $year = $month = 0;
        if (preg_match('/^(\d{4})-(\d{2})$/', $period, $m)) {
            $year = (int) $m[1];
            $month = (int) $m[2];
        } else {
            $errors['period'] = ['Ay alanı YYYY-MM biçiminde olmalı.'];
        }

        $value = self::parseDecimal($valueRaw);
        if ($value === null || $value <= 0) {
            $errors['value'] = ['Geçerli bir pozitif sayı girin.'];
        }

        if ($errors) {
            $this->bagErrors($errors, ['period' => $period, 'value' => $valueRaw, 'notes' => $notes]);
            \redirect('/yonetim/sistem/enflasyon-kaynaklari/' . $code . '/aylik-veri');
        }

        try {
            $this->repo->addMonthlyValue(
                $code,
                $year,
                $month,
                (float) $value,
                $notes !== '' ? $notes : null,
                SimpleAuth::user()['username'] ?? 'unknown'
            );
        } catch (\Throwable $e) {
            \flash('source_error', $e->getMessage());
            \redirect('/yonetim/sistem/enflasyon-kaynaklari/' . $code . '/aylik-veri');
        }

        \flash('source_success', "Aylık veri eklendi: {$period} → " . number_format((float) $value, 4, ',', '.'));
        \redirect('/yonetim/sistem/enflasyon-kaynaklari/' . $code . '/aylik-veri');
    }

    public function dataDelete(array $params): void
    {
        if (!\csrf_check((string) ($_POST['_csrf'] ?? ''))) {
            \flash('source_error', 'Oturum doğrulaması başarısız.');
            \redirect('/yonetim/sistem/enflasyon-kaynaklari');
        }

        $code = $params['code'] ?? '';
        $period = $params['period'] ?? '';

        try {
            $this->repo->deleteMonthlyValue($code, $period);
            \flash('source_success', "Aylık veri silindi: {$period}.");
        } catch (\Throwable $e) {
            \flash('source_error', $e->getMessage());
        }
        \redirect('/yonetim/sistem/enflasyon-kaynaklari/' . $code . '/aylik-veri');
    }

    // ---- Yardımcılar ------------------------------------------------------------------------

    /** @return array<string,mixed> */
    private function collectFormFields(): array
    {
        return [
            'code'          => trim((string) ($_POST['code'] ?? '')),
            'name'          => trim((string) ($_POST['name'] ?? '')),
            'description'   => trim((string) ($_POST['description'] ?? '')),
            'unit'          => (string) ($_POST['unit'] ?? 'index'),
            'base_period'   => trim((string) ($_POST['base_period'] ?? '')),
            'color_hex'     => trim((string) ($_POST['color_hex'] ?? '#2A5C6B')),
            'display_order' => (int) ($_POST['display_order'] ?? 100),
            'is_active'     => isset($_POST['is_active']) ? (bool) $_POST['is_active'] : true,
        ];
    }

    /** @return array<string, array<int, string>> */
    private function validateSource(array $data, bool $isCreate): array
    {
        $errors = [];
        if ($isCreate) {
            $code = strtolower($data['code'] ?? '');
            if ($code === '') {
                $errors['code'][] = 'Kod boş olamaz.';
            } elseif (!preg_match('/^[a-z][a-z0-9_]{2,63}$/', $code)) {
                $errors['code'][] = 'Kod 3-64 karakter, küçük harf/rakam/altçizgi olmalı (harfle başlamalı).';
            } elseif ($this->repo->isReservedCode($code)) {
                $errors['code'][] = '"tuik_" veya "enag_" ile başlayan kodlar rezerve. Örn: "uysa_et_endeksi".';
            }
        }

        $name = $data['name'] ?? '';
        if (mb_strlen($name) < 3 || mb_strlen($name) > 150) {
            $errors['name'][] = 'Ad 3-150 karakter olmalı.';
        }

        if (mb_strlen($data['description'] ?? '') > 2000) {
            $errors['description'][] = 'Açıklama en fazla 2000 karakter olabilir.';
        }

        if (!isset(InflationSourceRepository::UNITS[$data['unit'] ?? 'index'])) {
            $errors['unit'][] = 'Birim seçimi geçersiz.';
        }

        if (($data['base_period'] ?? '') !== '' && mb_strlen($data['base_period']) > 32) {
            $errors['base_period'][] = 'Baz dönem en fazla 32 karakter olabilir.';
        }

        if (!preg_match('/^#?[0-9A-Fa-f]{6}$/', $data['color_hex'] ?? '')) {
            $errors['color_hex'][] = 'Renk #RRGGBB biçiminde olmalı.';
        }

        $order = (int) ($data['display_order'] ?? 100);
        if ($order < 1 || $order > 9999) {
            $errors['display_order'][] = 'Sıralama 1-9999 aralığında olmalı.';
        }

        return $errors;
    }

    /** @return array{code:string,name:string,description:string,unit:string,base_period:string,color_hex:string,display_order:int,is_active:bool} */
    private function emptyRecord(): array
    {
        return [
            'code' => '',
            'name' => '',
            'description' => '',
            'unit' => 'index',
            'base_period' => '',
            'color_hex' => '#2A5C6B',
            'display_order' => 100,
            'is_active' => true,
        ];
    }

    private function bagErrors(array $errors, array $oldInput): void
    {
        $_SESSION['_form_errors'] = $errors;
        \flash_old($oldInput);
    }

    /** @return array<string, array<int, string>> */
    private function popErrors(): array
    {
        $errors = $_SESSION['_form_errors'] ?? [];
        unset($_SESSION['_form_errors']);
        return $errors;
    }

    private static function parseDecimal(string $raw): ?float
    {
        if ($raw === '') return null;
        $normalized = $raw;
        if (substr_count($raw, ',') === 1 && substr_count($raw, '.') > 0) {
            $normalized = str_replace('.', '', $raw);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (substr_count($raw, ',') === 1) {
            $normalized = str_replace(',', '.', $raw);
        }
        if (!is_numeric($normalized)) return null;
        return (float) $normalized;
    }

    private function render(string $view, array $data): string
    {
        $user = SimpleAuth::user();
        $content = \view($view, $data);
        return \layout('app', $content, [
            'title'  => 'Enflasyon Kaynak Yönetimi — Yönetim',
            'authed' => true,
            'user'   => $user,
        ]);
    }

    private function notFound(string $msg): string
    {
        http_response_code(404);
        return \view('errors.404');
    }
}

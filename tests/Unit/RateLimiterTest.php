<?php

declare(strict_types=1);

use App\Services\RateLimiter;
use Tests\TestRunner;

TestRunner::group('RateLimiter — sliding window', function () {

    $tmpDir = sys_get_temp_dir() . '/yh_test_rl_' . uniqid();
    mkdir($tmpDir, 0755, true);

    TestRunner::run('Limit altında allow=true', function () use ($tmpDir) {
        $rl = new RateLimiter('test_alpha', limit: 3, windowSeconds: 60, storageDir: $tmpDir);
        $rl->reset('1.2.3.4');
        TestRunner::assertTrue($rl->allow('1.2.3.4'), 'İstek 1');
        TestRunner::assertTrue($rl->allow('1.2.3.4'), 'İstek 2');
        TestRunner::assertTrue($rl->allow('1.2.3.4'), 'İstek 3');
    });

    TestRunner::run('Limit aşımında allow=false', function () use ($tmpDir) {
        $rl = new RateLimiter('test_beta', limit: 2, windowSeconds: 60, storageDir: $tmpDir);
        $rl->reset('5.5.5.5');
        TestRunner::assertTrue($rl->allow('5.5.5.5'));
        TestRunner::assertTrue($rl->allow('5.5.5.5'));
        TestRunner::assertFalse($rl->allow('5.5.5.5'), '3. istek limit dışı olmalı');
    });

    TestRunner::run('Farklı anahtarlar bağımsız sayılır', function () use ($tmpDir) {
        $rl = new RateLimiter('test_gamma', limit: 1, windowSeconds: 60, storageDir: $tmpDir);
        $rl->reset('a');
        $rl->reset('b');
        TestRunner::assertTrue($rl->allow('a'));
        TestRunner::assertTrue($rl->allow('b'), "'b' anahtarı 'a' dolu olsa bile geçmeli");
        TestRunner::assertFalse($rl->allow('a'));
    });

    TestRunner::run('remaining() doğru hesaplanır', function () use ($tmpDir) {
        $rl = new RateLimiter('test_delta', limit: 5, windowSeconds: 60, storageDir: $tmpDir);
        $rl->reset('x');
        $rl->allow('x');
        $rl->allow('x');
        TestRunner::assertSame(3, $rl->remaining('x'), '5 - 2 = 3 kalmalı');
    });

    TestRunner::run('reset() sayacı sıfırlar', function () use ($tmpDir) {
        $rl = new RateLimiter('test_eps', limit: 2, windowSeconds: 60, storageDir: $tmpDir);
        $rl->allow('z');
        $rl->allow('z');
        TestRunner::assertFalse($rl->allow('z'));
        $rl->reset('z');
        TestRunner::assertTrue($rl->allow('z'), 'Reset sonrası tekrar geçmeli');
    });

    TestRunner::run('Tehlikeli karakter bucket adında sanitize edilir', function () use ($tmpDir) {
        $rl = new RateLimiter('../../etc/passwd', limit: 1, windowSeconds: 60, storageDir: $tmpDir);
        $rl->allow('test');
        $files = array_values(array_filter(
            scandir($tmpDir) ?: [],
            static fn(string $f) => $f !== '.' && $f !== '..'
        ));
        $hasTraversal = false;
        foreach ($files as $f) {
            if (str_contains($f, '..') || str_contains($f, '/')) $hasTraversal = true;
        }
        TestRunner::assertFalse($hasTraversal, 'Path traversal payload sanitize edilmeli');
    });
});

<?php

declare(strict_types=1);

/**
 * Demo (geliştirme) kimlik doğrulama yapılandırması.
 *
 * UYARI: Bu dosya yalnızca Faz 0.5 demo prototipi içindir (ADR-014).
 * Faz 1.0a'da Laravel auth + DB tabanlı kullanıcı sistemi devreye girecek;
 * bu dosya o zaman kaldırılacak. Şifreler Argon2id ile hash'li (CLAUDE.md kuralı).
 */

return [
    /*
     * Hesap doğrulama: kullanıcı adına göre case-sensitive arama.
     * Şifreler Argon2id hash'li ("1234" plaintext'i password_verify ile çözülür).
     */
    'demo_users' => [
        'OFU' => [
            'password_hash' => '$argon2id$v=19$m=65536,t=4,p=1$T3JuMVpSb2EzV2hQS3A0cA$SaGO/OlDJS8iaydGv6jkJCBQkcZdaAop2uYJS1Z4lUY',
            'role'          => 'admin',
            'display_name'  => 'Ömer M.',
            'email'         => 'omer@uysa.com.tr',
            'panel_route'   => '/yonetim',
        ],
        'Uysa' => [
            'password_hash' => '$argon2id$v=19$m=65536,t=4,p=1$VFFwcVdGVFZiN2IvUzMzcA$5o+66OMMQG80cQU/3OpQl85HJEIRpubSsBc3LDO3J3s',
            'role'          => 'supplier',
            'display_name'  => 'UYSA Yemek (Demo Yemekçi)',
            'email'         => 'yemekci@uysa.com.tr',
            'panel_route'   => '/yemekci',
        ],
    ],

    /*
     * Session yapılandırması — production'da PHP php.ini veya Laravel config'inden okunur.
     */
    'session' => [
        'name'      => 'yemekhaneci_session',
        'lifetime'  => 7200,    // 2 saat
        'http_only' => true,
        'secure'    => false,   // HTTPS'te true (production'da .env'den)
        'same_site' => 'Lax',
    ],

    /*
     * Rate limit (Redis henüz aktif değil; demo aşamasında session-tabanlı sayaç).
     */
    'rate_limits' => [
        'login_per_min' => 5,
    ],
];

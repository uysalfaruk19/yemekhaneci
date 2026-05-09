<?php

declare(strict_types=1);

/**
 * PHPUnit bootstrap — composer autoload + helpers.
 */

require __DIR__ . '/../vendor/autoload.php';

if (!function_exists('app_path')) {
    require __DIR__ . '/../app/Helpers/functions.php';
}

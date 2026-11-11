<?php

/*
 * Vercel entry point. Vercel's disk is read-only except /tmp, so Laravel's
 * storage folder and bootstrap caches are moved there before booting.
 */
$writable = '/tmp/laravel';

foreach (['storage/framework/cache/data', 'storage/framework/views', 'storage/framework/sessions', 'storage/logs', 'storage/app/public', 'bootstrap'] as $directory) {
    if (! is_dir("{$writable}/{$directory}")) {
        mkdir("{$writable}/{$directory}", 0777, true);
    }
}

$_ENV['LARAVEL_STORAGE_PATH'] = $_SERVER['LARAVEL_STORAGE_PATH'] = "{$writable}/storage";

foreach (['APP_SERVICES_CACHE' => 'services.php', 'APP_PACKAGES_CACHE' => 'packages.php', 'APP_CONFIG_CACHE' => 'config.php', 'APP_ROUTES_CACHE' => 'routes.php', 'APP_EVENTS_CACHE' => 'events.php'] as $key => $file) {
    $_ENV[$key] = $_SERVER[$key] = "{$writable}/bootstrap/{$file}";
}

require __DIR__.'/../public/index.php';

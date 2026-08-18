<?php

/**
 * Temporary Deployment Fix Helper for daily.konekin.space
 * Runs Artisan migrations and clears cached routes/config.
 */

define('LARAVEL_START', microtime(true));

$baseDir = dirname(__DIR__);

// 1. Remove cached bootstrap route/config files directly if they exist
$cacheFiles = [
    $baseDir . '/bootstrap/cache/routes-v7.php',
    $baseDir . '/bootstrap/cache/routes.php',
    $baseDir . '/bootstrap/cache/config.php',
    $baseDir . '/bootstrap/cache/services.php',
];

$clearedFiles = [];
foreach ($cacheFiles as $file) {
    if (file_exists($file)) {
        @unlink($file);
        $clearedFiles[] = basename($file);
    }
}

// 2. Bootstrap Laravel application
require $baseDir . '/vendor/autoload.php';
$app = require_once $baseDir . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// 3. Run artisan commands
$output = [];

try {
    $kernel->call('migrate', ['--force' => true]);
    $output[] = "Migrate: " . trim($kernel->output());
} catch (\Throwable $e) {
    $output[] = "Migrate Error: " . $e->getMessage();
}

try {
    $kernel->call('route:clear');
    $output[] = "Route Clear: " . trim($kernel->output());
} catch (\Throwable $e) {
    $output[] = "Route Clear Error: " . $e->getMessage();
}

try {
    $kernel->call('config:clear');
    $output[] = "Config Clear: " . trim($kernel->output());
} catch (\Throwable $e) {
    $output[] = "Config Clear Error: " . $e->getMessage();
}

try {
    $kernel->call('view:clear');
    $output[] = "View Clear: " . trim($kernel->output());
} catch (\Throwable $e) {
    $output[] = "View Clear Error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'cleared_cache_files' => $clearedFiles,
    'artisan_output' => $output,
], JSON_PRETTY_PRINT);

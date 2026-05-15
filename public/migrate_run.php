<?php
if (($_GET['token'] ?? '') !== 'at2026mig') {
    http_response_code(403);
    die('Forbidden');
}

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo '<pre>';
Artisan::call('migrate', ['--force' => true]);
echo htmlspecialchars(Artisan::output());
echo '</pre>';
echo 'Listo.';

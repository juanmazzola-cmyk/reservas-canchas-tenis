<?php
// ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

try {
    Artisan::call('migrate', ['--force' => true]);
    echo '<pre>' . htmlspecialchars(Artisan::output()) . '</pre>';
    echo '<br>Listo. BORRÁ ESTE ARCHIVO.';
} catch (Exception $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}

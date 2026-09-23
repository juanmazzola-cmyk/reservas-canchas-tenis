<?php
// ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$resultados = [];

Artisan::call('config:clear');
$resultados[] = '✓ config:clear: ' . trim(Artisan::output());

echo implode('<br>', $resultados);
echo '<br>Listo. BORRÁ ESTE ARCHIVO.';

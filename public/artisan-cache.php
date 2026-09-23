<?php
// ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$resultados = [];

Artisan::call('config:cache');
$resultados[] = '✓ config:cache: ' . trim(Artisan::output());

Artisan::call('route:cache');
$resultados[] = '✓ route:cache: ' . trim(Artisan::output());

Artisan::call('view:cache');
$resultados[] = '✓ view:cache: ' . trim(Artisan::output());

Artisan::call('event:cache');
$resultados[] = '✓ event:cache: ' . trim(Artisan::output());

echo implode('<br>', $resultados);
echo '<br>Listo. BORRÁ ESTE ARCHIVO.';

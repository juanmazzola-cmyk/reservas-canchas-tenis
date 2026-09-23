<?php
// ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$resultados = [];

Artisan::call('view:clear');
$resultados[] = '✓ view:clear: ' . trim(Artisan::output());

echo implode('<br>', $resultados);
echo '<br>Listo. BORRÁ ESTE ARCHIVO.';

<?php
// BORRAR ESTE ARCHIVO DESPUÉS DE EJECUTAR
define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (Schema::hasColumn('users', 'dni')) {
    echo 'La columna dni ya existe. No se necesita migrar.';
} else {
    Schema::table('users', function (Blueprint $table) {
        $table->string('dni', 20)->nullable()->unique()->after('apellido');
    });
    echo 'DONE: columna dni agregada correctamente.';
}

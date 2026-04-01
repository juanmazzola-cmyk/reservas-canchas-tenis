<?php
// Script temporal para correr migraciones en produccion
// BORRAR este archivo despues de usarlo

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

header('Content-Type: text/plain');

$exitCode = $kernel->call('migrate', ['--force' => true]);

echo $kernel->output();
echo PHP_EOL . 'Exit code: ' . $exitCode;

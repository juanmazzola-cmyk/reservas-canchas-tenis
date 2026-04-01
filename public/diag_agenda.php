<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

header('Content-Type: text/plain');

try {
    $app->make('config')->set('app.debug', true);

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $request = Illuminate\Http\Request::create('/agenda', 'GET');

    // Simular usuario autenticado si hay sesión
    $response = $kernel->handle($request);
    echo 'STATUS: ' . $response->getStatusCode() . PHP_EOL;
    if ($response->getStatusCode() >= 400) {
        echo $response->getContent();
    } else {
        echo 'OK';
    }
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . PHP_EOL;
    echo 'FILE: ' . $e->getFile() . ':' . $e->getLine() . PHP_EOL;
    echo PHP_EOL . $e->getTraceAsString();
}

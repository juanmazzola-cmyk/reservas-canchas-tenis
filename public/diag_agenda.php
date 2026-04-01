<?php
header('Content-Type: text/plain');
$log = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($log)) {
    echo 'Log no encontrado: ' . $log;
    exit;
}
// Mostrar las últimas 100 líneas
$lines = file($log);
$last = array_slice($lines, -100);
echo implode('', $last);

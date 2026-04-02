<?php
// BORRAR ESTE ARCHIVO DESPUÉS DE EJECUTAR
chdir(dirname(__DIR__));
$output = shell_exec('php artisan migrate --force 2>&1');
echo '<pre>' . htmlspecialchars($output) . '</pre>';

<?php
// ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO

// Leer credenciales del .env de Laravel
$envFile = __DIR__ . '/../.env';
$env = [];
foreach (file($envFile) as $line) {
    $line = trim($line);
    if ($line && !str_starts_with($line, '#') && str_contains($line, '=')) {
        [$key, $val] = explode('=', $line, 2);
        $env[trim($key)] = trim($val);
    }
}

$host = $env['DB_HOST'] ?? 'localhost';
$db   = $env['DB_DATABASE'] ?? '';
$user = $env['DB_USERNAME'] ?? '';
$pass = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $cols = $pdo->query("SHOW COLUMNS FROM users LIKE 'nro_socio'")->fetchAll();
    if ($cols) {
        echo "✓ La columna nro_socio ya existe. No se hizo nada.";
    } else {
        $pdo->exec("ALTER TABLE users ADD COLUMN nro_socio VARCHAR(5) NULL AFTER es_socio");
        echo "✓ Columna nro_socio agregada correctamente. BORRÁ ESTE ARCHIVO.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

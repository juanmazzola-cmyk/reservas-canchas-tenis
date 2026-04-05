<?php
// ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO
$host = 'localhost';
$db   = 'c2761827_reservas';
$user = 'c2761827_reservas';
$pass = 'Reservas2026';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si la columna ya existe
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

<?php
// ELIMINAR ESTE ARCHIVO DESPUÉS DE USARLO

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

    $log = [];

    // estado_autorizacion
    $cols = $pdo->query("SHOW COLUMNS FROM pagos LIKE 'estado_autorizacion'")->fetchAll();
    if ($cols) {
        $log[] = "✓ estado_autorizacion ya existe, no se tocó.";
    } else {
        $pdo->exec("ALTER TABLE pagos ADD COLUMN estado_autorizacion ENUM('aprobado_ia','rechazado_ia','pendiente_admin','aprobado_admin') NOT NULL DEFAULT 'aprobado_ia' AFTER estado");
        $log[] = "✓ estado_autorizacion agregada.";
    }

    // motivo_rechazo
    $cols = $pdo->query("SHOW COLUMNS FROM pagos LIKE 'motivo_rechazo'")->fetchAll();
    if ($cols) {
        $log[] = "✓ motivo_rechazo ya existe, no se tocó.";
    } else {
        $pdo->exec("ALTER TABLE pagos ADD COLUMN motivo_rechazo TEXT NULL AFTER estado_autorizacion");
        $log[] = "✓ motivo_rechazo agregada.";
    }

    // autorizado_por
    $cols = $pdo->query("SHOW COLUMNS FROM pagos LIKE 'autorizado_por'")->fetchAll();
    if ($cols) {
        $log[] = "✓ autorizado_por ya existe, no se tocó.";
    } else {
        $pdo->exec("ALTER TABLE pagos ADD COLUMN autorizado_por BIGINT UNSIGNED NULL AFTER motivo_rechazo");
        $log[] = "✓ autorizado_por agregada.";
    }

    // autorizado_at
    $cols = $pdo->query("SHOW COLUMNS FROM pagos LIKE 'autorizado_at'")->fetchAll();
    if ($cols) {
        $log[] = "✓ autorizado_at ya existe, no se tocó.";
    } else {
        $pdo->exec("ALTER TABLE pagos ADD COLUMN autorizado_at TIMESTAMP NULL AFTER autorizado_por");
        $log[] = "✓ autorizado_at agregada.";
    }

    foreach ($log as $line) echo $line . "<br>";
    echo "<br><strong>BORRÁ ESTE ARCHIVO del servidor.</strong>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

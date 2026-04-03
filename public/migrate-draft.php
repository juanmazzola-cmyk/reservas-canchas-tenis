<?php
// BORRAR DESPUÉS DE USAR
$env = [];
foreach (file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) continue;
    [$k, $v] = explode('=', $line, 2);
    $env[trim($k)] = trim(trim($v), '"\'');
}
try {
    $pdo = new PDO(
        "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_DATABASE']};charset=utf8",
        $env['DB_USERNAME'], $env['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) { die('Error conexión: ' . $e->getMessage()); }

$msg = '';
if (isset($_GET['run'])) {
    try {
        $pdo->exec("ALTER TABLE reservas MODIFY COLUMN estado ENUM('DRAFT','PENDING','PENDING_REVIEW','AUTHORIZED','REJECTED','SUSPENDIDA','PARTIAL_PAYMENT') DEFAULT 'PENDING'");
        $msg = "<p style='color:green;font-weight:bold'>✓ Migración ejecutada correctamente. Podés borrar este script.</p>";

        // Registrar en migrations
        $existe = $pdo->query("SELECT COUNT(*) FROM migrations WHERE migration = '2026_04_03_000001_add_draft_to_reservas_estado'")->fetchColumn();
        if (!$existe) {
            $batch = $pdo->query("SELECT MAX(batch) FROM migrations")->fetchColumn();
            $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)")
                ->execute(['2026_04_03_000001_add_draft_to_reservas_estado', $batch + 1]);
        }
    } catch (Exception $e) {
        $msg = "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
    }
}

// Verificar estado actual
$col = $pdo->query("SHOW COLUMNS FROM reservas LIKE 'estado'")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Migración DRAFT</title></head>
<body style="font-family:sans-serif;max-width:500px;margin:40px auto;padding:20px">
<?= $msg ?>
<h2>Estado actual de la columna</h2>
<pre style="background:#f5f5f5;padding:12px;border-radius:6px"><?= htmlspecialchars(print_r($col, true)) ?></pre>
<?php if (!str_contains($col['Type'] ?? '', 'DRAFT')): ?>
    <p style="color:red">⚠ El estado DRAFT no está en el ENUM todavía.</p>
    <a href="?run=1" style="background:#0057a8;color:white;padding:10px 24px;border-radius:6px;text-decoration:none;display:inline-block;margin-top:12px">
        Ejecutar migración
    </a>
<?php else: ?>
    <p style="color:green">✓ El estado DRAFT ya está en el ENUM. Todo OK.</p>
<?php endif; ?>
</body></html>

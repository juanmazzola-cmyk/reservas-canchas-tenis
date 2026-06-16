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

    // Detectar reservas corruptas: esta_pagado=true pero tienen pagos PENDIENTES
    $stmt = $pdo->query("
        SELECT r.id, r.dia, r.hora, r.cancha_id
        FROM reservas r
        WHERE r.esta_pagado = 1
          AND r.estado = 'AUTHORIZED'
          AND EXISTS (
              SELECT 1 FROM pagos p
              WHERE p.reserva_id = r.id
                AND p.estado = 'PENDIENTE'
          )
    ");
    $afectadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($afectadas)) {
        echo "<strong>✓ No se encontraron reservas corruptas. Todo está en orden.</strong>";
    } else {
        echo "<p>Reservas corruptas encontradas: <strong>" . count($afectadas) . "</strong></p>";
        echo "<ul>";
        foreach ($afectadas as $r) {
            echo "<li>ID {$r['id']} — {$r['dia']} {$r['hora']} Cancha {$r['cancha_id']}</li>";
        }
        echo "</ul>";

        // Corregir: esta_pagado=false, estado=PARTIAL_PAYMENT
        $updated = $pdo->exec("
            UPDATE reservas r
            SET r.esta_pagado = 0, r.estado = 'PARTIAL_PAYMENT'
            WHERE r.esta_pagado = 1
              AND r.estado = 'AUTHORIZED'
              AND EXISTS (
                  SELECT 1 FROM pagos p
                  WHERE p.reserva_id = r.id
                    AND p.estado = 'PENDIENTE'
              )
        ");

        echo "<br><strong>✓ $updated reserva(s) corregida(s) → esta_pagado=false, estado=PARTIAL_PAYMENT.</strong>";
    }

    echo "<br><br><strong style='color:red'>BORRÁ ESTE ARCHIVO del servidor.</strong>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
